# State Machines

Five state machines wired together via EventBridge. ASL JSON definitions in this directory; CDK reads them at synth time. Per-SM diagrams in `./diagrams/`.

## Pipeline

```
GuardDuty NO_THREATS_FOUND (native EventBridge)
   ↓
move-clean-document.asl.json     copy upload → clean bucket, delete original
   ↓ S3 Object Created on clean-documents bucket
classification.asl.json          Textract Lending → type + confidence + page breakdown
   ↓ DocumentProcessing-Classified  (EventBridge rule filters on classification + numeric matchers)
extraction.asl.json              Bedrock Data Automation, custom blueprint, splitter enabled
   ↓ DocumentProcessing-Extracted
context-gathering.asl.json       applicant profile from DynamoDB
   ↓ DocumentProcessing-ContextGathered
claude-analysis.asl.json         Bedrock managed prompt + forced tool use, persist to DDB
   ↓ DocumentProcessing-Completed
```

## State machines

[//]: # "### 1. `move-clean-document.asl.json`"
[//]: # "📊 **[Diagram](./diagrams/move-clean-document.md)**"
[//]: #
[//]: # "Copy a GuardDuty-verified clean document from the upload bucket to the clean-documents bucket, then delete the original."
[//]: #
[//]: # "- **Trigger**: EventBridge rule on `aws.guardduty` / `GuardDuty Malware Protection Object Scan Result` filtered to `scanResultStatus: NO_THREATS_FOUND`. No polling."
[//]: # "- **Timeout**: 35 minutes (covers worst-case event delivery latency)."
[//]: # "- **Emits on failure**: `DocumentProcessing-MoveCleanDocumentFailed` (copy or delete step failed — file stays in upload bucket)."
[//]: # "- **No success event** — downstream classification triggers off `S3 Object Created` on the clean bucket."
[//]: #

### 2. `classification.asl.json`

📊 **[Diagram](./diagrams/classification.md)**

Textract Lending Analysis. Reports the dominant document type + numeric confidence. EventBridge routes downstream based on type and the numeric thresholds in `config/routing-policy.json`.

-   **Polling strategy**: uses `getLendingAnalysisSummary` (small response, returns `JobStatus` + Summary) for both polling and final fetch. Deliberately does **not** call `getLendingAnalysis` — that endpoint returns the full `Documents[]` extraction payload which can exceed Step Functions' 256 KB state limit on multi-page docs.
-   **JSONata `ExtractClassification`**: filters UNCLASSIFIED as noise, picks dominant from classified pages only, confidence = `dominantTypePages / classifiedPages`. Comparator is boolean (`$a.pageCount < $b.pageCount`), not numeric — JSONata's `$sort` quirk.
-   **S3 tag preservation**: reads existing tags and appends the `Classification` tag, preserving `ApplicationNumber`, `LicenceNumber`, etc.
-   **Emits**: `DocumentProcessing-Classified` with `{ classification, classificationConfidence, totalPages, classifiedPages, dominantTypePages, pageBreakdown, documentSizeBytes, textractJobId }`. One event regardless of doc type — routing is per-rule at the bus.
-   **Failure mode**: `INVALID_IMAGE_TYPE` (HEIC, WebP, DOCX) or other Textract job failure → routes to caseworker-review path (currently emits a typed failure event the test harness picks up).
-   **Timeout**: 15 minutes.

[//]: # "### 3. `extraction.asl.json`"
[//]: # "📊 **[Diagram](./diagrams/extraction.md)**"
[//]: #
[//]: # "Invokes Bedrock Data Automation against the CDK-managed `BdaProject` (custom `BankStatementBlueprint`)."
[//]: #
[//]: # "- **Trigger**: `DocumentProcessing-Classified` matching all of: `classification == 'BANK_STATEMENT'` AND `classificationConfidence >= minConfidence` AND `totalPages <= maxPages` AND `documentSizeBytes <= maxBytes`. All four values live in `config/routing-policy.json`."
[//]: # "- **BDA project config**: splitter enabled (multi-doc PDFs return per-segment output, only segments matching the blueprint get `custom_output`); image/video/audio modalities disabled; document granularity restricted to `DOCUMENT` only; bounding boxes off; markdown-only text format."
[//]: # "- **Custom blueprint**: signed balance numbers (overdraft markers detected by BDA, not by Claude). See `config/bank-statement-blueprint.json`."
[//]: # "- **Polling**: invoked async via `InvokeDataAutomationAsync`, status polled via `GetDataAutomationStatus`."
[//]: # "- **Emits**: `DocumentProcessing-Extracted` with `bedrockInvocationArn`, `bedrockInvocationId`, `extractedDataS3Bucket`, `extractedDataS3KeyPrefix`."
[//]: # "- **Timeout**: 15 minutes."
[//]: #
[//]: # "### 4. `context-gathering.asl.json`"
[//]: # "📊 **[Diagram](./diagrams/context-gathering.md)**"
[//]: #
[//]: # "Looks up the applicant profile from `DocumentProcessingStack-ApplicationContext` DynamoDB."
[//]: #
[//]: # "- **Reads**: `ApplicationNumber` tag from the S3 object → `GetItem` on the context table."
[//]: # "- **Projection**: `BuildContextWithData` (Pass state) constructs a stable `applicantProfile` shape from raw DDB columns. This projection is the **contract** with the Claude prompt — swap the data source (DDB today, prod API tomorrow) without touching anything downstream."
[//]: # "- **Fallback**: `UseEmptyContext` if the tag is missing or no DDB record. Worth tightening to fail-fast in production so missing-context doesn't silently produce misleading analysis."
[//]: # "- **Emits**: `DocumentProcessing-ContextGathered`."
[//]: # "- **Timeout**: 5 minutes (execution is <1 s)."
[//]: #
[//]: # "### 5. `claude-analysis.asl.json`"
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

```bash
# Edit ASL JSON
vi state-machines/classification.asl.json
```

CDK reads ASL files at synth time and embeds them in the state machine resources. No code change needed for ASL edits.

## Validating

Online: [ASL Validator](https://asl-validator.cloud/) or the AWS Step Functions Workflow Studio.

CLI:

```bash
aws stepfunctions validate-state-machine-definition \
  --definition file://classification.asl.json
```

## References

-   [Amazon States Language Specification](https://states-language.net/spec.html)
-   [Step Functions JSONata support](https://docs.aws.amazon.com/step-functions/latest/dg/transforming-data.html)
-   [EventBridge PutEvents integration](https://docs.aws.amazon.com/step-functions/latest/dg/connect-eventbridge.html)
