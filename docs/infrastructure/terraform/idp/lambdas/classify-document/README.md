# Document Classification Lambda

## Overview

This Lambda performs document classification as part of the document processing Step Function workflow.

It replaces the previous **Textract Lending Analysis** classification step with a Bedrock-powered classifier using a lightweight Claude model.

The Lambda receives a PDF stored in Amazon S3, sends it to Amazon Bedrock for visual document analysis, and returns a deterministic classification result that the Step Function uses for routing.

---

## Why this Lambda exists

The previous Textract Lending Analysis step only recognised a fixed set of lending-related document types.

This classifier supports configurable document types defined in `DOCUMENT_TYPES`, such as:

- Bank statements
- Transaction reports
- Newspaper advertisements
- Future document types added through configuration

Adding a new classification type should only require:

1. Adding the new type definition.
2. Adding any required Step Function routing logic.

The Lambda classification logic itself does not need to change.

---

## Architecture

```
S3 Object Created
        |
        v
Step Function
        |
        v
Classify Document Lambda
        |
        +--> Get PDF from S3
        |
        +--> Convert PDF to base64
        |
        +--> Invoke Claude model on Amazon Bedrock
        |
        +--> Calculate classification confidence
        |
        v
Return classification result
        |
        v
Step Function routing
```

---

## Why a Lambda is required

Amazon Bedrock Claude PDF requests require the document content to be supplied inline as base64.

There is no direct:

- S3 document reference
- URL source
- Files API integration

A PDF can become several MB after base64 encoding, which exceeds the Step Functions state payload limit.

The Lambda therefore handles:

1. Reading the PDF from S3.
2. Encoding the document.
3. Calling Bedrock.
4. Returning only the small classification response.

This keeps the Step Function state payload small.

---

## Why InvokeModel is used

This Lambda uses Bedrock `InvokeModel` instead of the Converse API.

Visual PDF understanding requires the model to analyse rendered document pages. Converse currently requires citations to be enabled for this workflow, which can result in text-only extraction behaviour.

`InvokeModel` provides direct visual document analysis without requiring citations.

---

## Classification approach

The model is responsible only for:

- Identifying the document type for each page.
- Returning page counts by document type.
- Providing classification reasoning.

The Lambda calculates:

- Dominant document type.
- Classification confidence.
- Classified page count.

This keeps routing decisions deterministic and auditable.

Example:

Input from model:

```json
{
    "pageBreakdown": [
        {
            "type": "BANK_STATEMENT",
            "pageCount": 8
        },
        {
            "type": "NEWSPAPER_ADVERT",
            "pageCount": 2
        }
    ],
    "totalPages": 10
}
```

Lambda output:

```json
{
    "classification": "BANK_STATEMENT",
    "classificationConfidence": 0.8,
    "totalPages": 10,
    "classifiedPages": 10,
    "dominantTypePages": 8
}
```

---

## Lambda Input

The Step Function invokes the Lambda with:

```json
{
    "bucket": "example-bucket",
    "key": "documents/2026/july/document.pdf",
    "modelId": "optional-bedrock-model-id",
    "maxBytes": 14680064
}
```

### Fields

| Field      | Required | Description                                    |
| ---------- | -------- | ---------------------------------------------- |
| `bucket`   | Yes      | S3 bucket containing the PDF                   |
| `key`      | Yes      | S3 object key                                  |
| `modelId`  | No       | Bedrock model ID override                      |
| `maxBytes` | No       | Maximum PDF size allowed for inline processing |

---

## Lambda Output

The Lambda returns:

```json
{
    "classification": "BANK_STATEMENT",
    "classificationConfidence": 0.95,
    "totalPages": 20,
    "classifiedPages": 20,
    "dominantTypePages": 19,
    "pageBreakdown": [
        {
            "type": "BANK_STATEMENT",
            "pageCount": 19
        },
        {
            "type": "UNCLASSIFIED",
            "pageCount": 1
        }
    ],
    "reasoning": "Pages contain bank letterhead, account information and statement periods.",
    "modelId": "eu.anthropic.claude-haiku-4-5-20251001-v1:0"
}
```

---

## Supported Document Types

Document types are defined in `DOCUMENT_TYPES`.

Current supported types:

| Type                 | Description                                 |
| -------------------- | ------------------------------------------- |
| `BANK_STATEMENT`     | Formal bank or building society statements  |
| `TRANSACTION_REPORT` | Financial exports and transaction listings  |
| `NEWSPAPER_ADVERT`   | Operator licence newspaper advertisements   |
| `UNCLASSIFIED`       | Pages that cannot be confidently classified |

---

## Size Limitations

Bedrock requests have payload limits, and PDFs are sent inline as base64.

The Lambda enforces a maximum PDF size:

```
14 MB
```

before downloading the object.

Files exceeding this limit fail with:

```
DocumentTooLargeForInlineClassification
```

The Step Function should catch this error and route the document to manual review or an alternative processing path.

---

## Environment Variables

| Variable         | Description                           | Default                        |
| ---------------- | ------------------------------------- | ------------------------------ |
| `MODEL_ID`       | Bedrock model or inference profile ID | Claude Haiku inference profile |
| `MAX_BYTES`      | Maximum PDF size                      | 14 MB                          |
| `BEDROCK_REGION` | Bedrock region override               | Lambda AWS region              |

---

## IAM Permissions Required

The Lambda role requires:

### S3

Read access to the source documents:

```text
s3:GetObject
s3:HeadObject
```

### Bedrock

Permission to invoke the selected model:

```text
bedrock:InvokeModel
```

---

## Dependencies

No third-party dependencies are required.

Uses AWS SDK v3 packages provided by the Lambda runtime:

- `@aws-sdk/client-s3`
- `@aws-sdk/client-bedrock-runtime`

Runtime:

```
Node.js 24.x
```

---

## Local Testing

The Lambda can be tested locally against a PDF file.

Example:

```bash
node index.mjs ./sample.pdf
```

The command uses your local AWS credentials and region configuration to call Bedrock.

---

## Error Handling

The Step Function should handle:

| Error                                     | Meaning                                     |
| ----------------------------------------- | ------------------------------------------- |
| `DocumentTooLargeForInlineClassification` | PDF exceeds Bedrock inline processing limit |
| Missing `bucket` or `key`                 | Invalid Lambda input                        |
| Model did not return tool call            | Unexpected Bedrock response                 |

---

## Design Principles

- Keep Step Function payloads small.
- Keep classification decisions deterministic.
- Keep document type definitions configurable.
- Avoid infrastructure changes when new document types are introduced.
- Use AI for document understanding, not workflow decisions.
