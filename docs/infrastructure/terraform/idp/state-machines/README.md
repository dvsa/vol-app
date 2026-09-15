# State Machines

State machines wired together via EventBridge. ASL JSON definitions in `modules/idp/state-machines/`; Terraform deploys them via `aws_sfn_state_machine`. Per-SM diagrams in `./diagrams/`.

## Pipeline

```
S3 Object Created (Financial_Evidence_Digital/YYYY/MM)
   ↓ EventBridge Object Created rule (prefix + year/month filter in SM)
classification.asl.json          Claude on Bedrock → type + confidence + page breakdown
   ↓ DocumentProcessing-Classified  (EventBridge rule filters on classification + numeric matchers)
extraction.asl.json              Bedrock Data Automation, custom blueprint, splitter enabled
   ↓ DocumentProcessing-Extracted
context-gathering.asl.json       applicant profile from DynamoDB
   ↓ DocumentProcessing-ContextGathered
claude-analysis.asl.json         Bedrock managed prompt + forced tool use, persist to DDB
   ↓ DocumentProcessing-Completed
```

## State machines

### 1. `classification.asl.json`

📊 **[Diagram](./diagrams/classification.md)**

Classifies PDFs via Claude on Amazon Bedrock. Reports the dominant document type + numeric confidence + page breakdown as a `DocumentProcessing-Classified` event; downstream routing is handled at the EventBridge bus.

- **Trigger**: EventBridge `Object Created` rule on the sabredav S3 bucket, filtered to the `Financial_Evidence_Digital/` key prefix. A JSONata `$now()` check inside the SM further narrows to the current year/month — no re-deploy needed on rollover.
- **Lambda**: `classify-document` — `GetObject` → base64 → Bedrock `InvokeModel` (Claude Haiku, forced `submit_classification` tool). PDF bytes never enter Step Functions state; only the small verdict is returned.
- **S3 tagging**: reads existing tags and appends a `Classification` tag, preserving `ApplicationNumber`, `LicenceNumber`, etc.
- **Emits**: `DocumentProcessing-Classified` with `{ classification, classificationConfidence, totalPages, classifiedPages, dominantTypePages, pageBreakdown, documentSizeBytes, modelId }`.
- **Failure mode**: `DocumentTooLargeForInlineClassification`, Bedrock errors, or unsupported content-type → `DocumentProcessing-ClassificationFailed`.
- **Timeout**: 15 minutes.

### 2. `extraction.asl.json`

📊 **[Diagram](./diagrams/extraction.md)**

Invokes Bedrock Data Automation against the Terraform-managed BDA project (custom `bank-statement` blueprint).

- **Trigger**: `DocumentProcessing-Classified` matching all of: `classification` in `[BANK_STATEMENT, TRANSACTION_REPORT]` AND `classificationConfidence >= 0.75` AND `totalPages <= 100` AND `documentSizeBytes <= 209715200`.
- **BDA project config**: splitter enabled (multi-doc PDFs return per-segment output, only segments matching the blueprint get `custom_output`); image/video/audio modalities disabled; document granularity `DOCUMENT` only; bounding boxes off; markdown-only text format.
- **Custom blueprint**: signed balance numbers (overdraft markers detected by BDA, not by Claude). Schema in `modules/idp/config/bank-statement-blueprint.json`.
- **Polling**: invoked async via `InvokeDataAutomationAsync`, status polled via `GetDataAutomationStatus`.
- **Emits**: `DocumentProcessing-Extracted` with `bedrockInvocationArn`, `bedrockInvocationId`, `extractedDataS3Bucket`, `extractedDataS3KeyPrefix`.
- **On skip** (document type not eligible): `DocumentProcessing-ExtractionSkipped`.
- **On failure**: `DocumentProcessing-ExtractionFailed`.
- **Timeout**: 30 minutes.

[//]: # "### 3. `context-gathering.asl.json` (future migration)"
[//]: # "📊 **[Diagram](./diagrams/context-gathering.md)**"
[//]: #
[//]: # "Looks up the applicant profile from DynamoDB."
[//]: #
[//]: # "### 4. `claude-analysis.asl.json` (future migration)"
[//]: # "📊 **[Diagram](./diagrams/claude-analysis.md)**"
[//]: #
[//]: # "DVSA validation via Claude Opus 4.7 against the Bedrock managed prompt."
[//]: #
[//]: # "- **Trigger**: `DocumentProcessing-ContextGathered`."
[//]: # "- **FetchCustomOutput / FetchStandardOutput**: invokes the `extract-s3-json-field` Lambda to pluck just `inference_result` and `document.representation.markdown` from BDA's result.json files. Bypasses Step Functions' 256 KB state limit — BDA's `inference_metadata` + `explanations` bloat is service-fixed and can't be configured away."
[//]: # "- **AnalyzeWithClaude**: `aws-sdk:bedrockruntime:converse` against the managed prompt version ARN, four `PromptVariables` (`applicant_profile`, `bank_statement_data`, `bank_statement_markdown`, `extraction_context`)."
[//]: # "- **Forced tool use**: prompt's `toolChoice` forces `submit_quality_check` — output is server-validated against the JSON Schema in `lib/prompts/bank-statement-check-tool-schema.json`."
[//]: # "- **Prompt caching**: system block (role + rules JSON) has `cachePoint: 'default'` — ~90% saving on cached input tokens after the first invocation."
[//]: # "- **ParseAnalysis**: reads `$.analysis.raw.Output.Message.Content[0].ToolUse.Input` (the validated tool input is the analysis result)."
[//]: # "- **PersistResult**: `PutItem` to `DocumentProcessingStack-Results`."
[//]: # "- **Emits**: `DocumentProcessing-Completed` on success, `DocumentProcessing-AnalysisFailed` on any failure."
[//]: # "- **Model**: `eu.anthropic.claude-opus-4-7` (cross-region inference profile)."
[//]: # "- **Timeout**: 10 minutes."

## Common ASL patterns

### Retry with exponential backoff

```json
{
    "Retry": [
        {
            "ErrorEquals": ["ThrottlingException"],
            "IntervalSeconds": 2,
            "MaxAttempts": 3,
            "BackoffRate": 2
        }
    ]
}
```

### Catch + route to failure

```json
{
    "Catch": [
        {
            "ErrorEquals": ["States.ALL"],
            "ResultPath": "$.error",
            "Next": "EmitFailureEvent"
        }
    ]
}
```

### Emit a typed event

```json
{
    "Type": "Task",
    "Resource": "arn:aws:states:::events:putEvents",
    "Parameters": {
        "Entries": [
            {
                "Detail": { "bucket.$": "$.bucket", "key.$": "$.key" },
                "DetailType": "DocumentProcessing-Completed",
                "Source": "custom.documentProcessing"
            }
        ]
    }
}
```

### JSONata (Pass state)

Used in `classification.asl.json` for the dominant-type calculation. Note JSONata's `$sort` takes a **boolean** comparator (`true` means `$a` should come after `$b`), not numeric like JavaScript.

```jsonata
$sort($breakdown, function($a, $b){ $a.pageCount < $b.pageCount })
```

## Modifying a step

Edit the ASL JSON in `modules/idp/state-machines/`, then run `terraform apply`. Terraform re-deploys the state machine definition automatically — no other changes needed for ASL edits.

## Validating

Online: [ASL Validator](https://asl-validator.cloud/) or the AWS Step Functions Workflow Studio.

CLI:

```bash
aws stepfunctions validate-state-machine-definition \
  --definition file://classification.asl.json
```

## References

- [Amazon States Language Specification](https://states-language.net/spec.html)
- [Step Functions JSONata support](https://docs.aws.amazon.com/step-functions/latest/dg/transforming-data.html)
- [EventBridge PutEvents integration](https://docs.aws.amazon.com/step-functions/latest/dg/connect-eventbridge.html)
