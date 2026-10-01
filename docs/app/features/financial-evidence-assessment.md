---
sidebar_position: 50
title: Financial Evidence Assessment
---

# Financial Evidence Assessment

The financial evidence assessment page in the internal application shows caseworkers what the
IDP pipeline made of each bank statement an applicant uploaded. It is behind the `IDP` feature
toggle and appears as a section on applications, variations and licences.

## Data flow

1. The IDP pipeline analyses a document and writes an **analysis report** to S3:
   `{"metadata": {...}, "applicantProfile": {...}, "analysis": {...}}`. `metadata` is provenance
   (bucket, key, execution ARN, classification, `schemaVersion`, `promptVersionArn`),
   `applicantProfile` is what the model was told about the applicant, and `analysis` is the
   model's output.
2. `StoreDocumentAnalysisResult` (API) fetches the report and stores it **verbatim** in
   `document_analysis.result`. That column is the audit record of what the pipeline produced and
   is never reshaped.
3. In the same UPDATE it stores the report mapped to the **assessment payload** in
   `document_analysis.result_normalised`, using `AnalysisResultNormaliser::normalise()`. A
   report with no readable analysis is still a successful analysis: `result_normalised` is left
   `NULL` and a warning is logged.
4. `DocumentAnalysisList` (API) returns `resultNormalised` plus the document's id, description,
   filename and date for each analysis. Every stored payload goes out through
   `AnalysisResultNormaliser::fromStored()`, so it is always at the current version whatever
   version the row was written with. The raw result is deliberately not returned: it holds the
   applicant profile and infrastructure provenance.
5. `FinancialEvidenceAssessmentTab` (internal) turns one analysis into one tab: labels,
   formatted values, tag colours and the issue count. The view escapes everything it prints,
   because remarks and values are model-generated text.

Caseworker annotations (a later ticket) are stored separately and merged over the assessment
payload at read time, row by row, with the annotation winning. Nothing is ever written back
into `result` or `result_normalised`.

## The model's output

The model is forced to call the `submit_quality_check` tool, whose schema lives in
`infra/terraform/modules/idp/config/bank-statement-check-tool-schema.json`. The rules it follows,
including the six summary categories and their definitions, are in `bank-statement-checks.json`
beside it. The output has:

| Key | Contents |
|---|---|
| `core_checks` | One entry per FI check (`FI01`–`FI10`, `FI16`): `result` (`Pass`, `Fail` or `Skipped`), `workingOut` and `remark`. |
| `statement_details` | Facts read from the statement: bank name and address, account holder name, issue date, period start and end, the 4-balance average and the number of large deposits. `null` when not shown or not determined. |
| `category_remarks` | One caseworker-facing sentence per summary category. |

`metadata.schemaVersion` says which shape a stored report has. Reports written before the stamp
existed carry no `schemaVersion` and have `core_checks` only; the normaliser reads both.

## The assessment payload (`result_normalised`)

```json
{
  "version": 1,
  "rows": {
    "bank":            { "flag": null,   "remark": null,  "value": "Example Bank", "checks": {} },
    "bankAddress":     { "flag": null,   "remark": null,  "value": "1 Example Street", "checks": {} },
    "authenticity":    { "flag": "pass", "remark": "...", "value": null, "checks": { "FI01": { "result": "pass", "remark": "..." }, "FI03": {}, "FI04": {} } },
    "name":            { "flag": "pass", "remark": "...", "value": "Example Haulage Ltd", "checks": { "FI06": {}, "FI07": {}, "FI08": {} } },
    "statementDate":   { "flag": "fail", "remark": "...", "value": "2026-08-31", "checks": { "FI10": {} } },
    "statementPeriod": { "flag": "pass", "remark": "...", "value": { "start": "2026-08-01", "end": "2026-08-31" }, "checks": { "FI05": {} } },
    "averageFunds":    { "flag": "pass", "remark": "...", "value": 15321.5, "checks": { "FI09": {}, "FI02": {} } },
    "largeDeposit":    { "flag": "fail", "remark": "...", "value": 2, "checks": { "FI16": {} } }
  }
}
```

- **Rows are a map keyed by a stable name, and every row has the same four keys.** That is what
  lets an annotation be merged over a row with a plain `array_merge()`.
- **`flag`** is derived from the row's checks, never taken from the model: any `fail` fails the
  row; otherwise any `pass` passes it; otherwise it is `skipped`. Skipped checks are neutral
  because `FI08` is skipped for every limited company and `FI02` whenever `FI09` calculates an
  average. Bank and bank address are information only and have no flag.
- **`remark`** is the model's category remark, falling back for older reports to the remark of
  the first failing check, then the first passing, then the first skipped.
- **`value`** is typed per row (text, ISO date, `{start, end}`, number, integer) and is `null`
  for a skipped row, which the tab shows as "-".
- **`checks`** keeps the per-check result and remark for traceability back to the raw report.
  FI codes are internal identifiers and are never shown to caseworkers.
- **`version`** is the version of this payload's shape and mapping. Stored rows are never
  rewritten when either changes: a row keeps the version it was written with, and a reader
  upcasts older versions to the current shape on the way out of the API. Consumers only ever
  see the current version. The raw report is kept, so a row can be regenerated from scratch if
  a normalisation bug ever makes that necessary, but that is a deliberate correction, not part
  of a version bump.

## Where things live

| Concern | Location |
|---|---|
| Rules, categories, tool schema, prompt | `infra/terraform/modules/idp/config/`, `infra/terraform/modules/idp/main.tf` |
| Report metadata stamp | `infra/terraform/modules/idp/state-machines/ai-analysis.asl.json` (`BuildAnalysisReport`) |
| Normaliser | `app/api/module/Api/src/Service/Idp/AnalysisResultNormaliser/`: `AnalysisResultNormaliser` (entry point), `NormalisedResult` (the current shape, owns `VERSION`), `ReportMapper` (report to current shape), `Version/` (one mapper per superseded version) |
| Storage | `app/api/module/Api/src/Domain/CommandHandler/Document/StoreDocumentAnalysisResult.php`, `Repository/DocumentAnalysis::recordSuccess()` |
| Query | `app/api/module/Api/src/Domain/QueryHandler/Document/DocumentAnalysisList.php` |
| Tab content | `app/internal/module/Olcs/src/Data/Mapper/FinancialEvidenceAssessmentTab.php`, `view/sections/lva/financial-evidence-assessment.phtml` |
| Schema | `document_analysis` and `document_analysis_hist` in olcs-etl |

Changing the FI-to-category mapping or the payload shape means bumping
`NormalisedResult::VERSION`, changing `ReportMapper` to produce the new shape, and adding a
`VersionMapperInterface` implementation for the superseded version (registered in
`AnalysisResultNormaliserFactory`), so existing rows keep their stored payload and still come
out in the current shape.
Annotations are keyed by row name, so the same upcast must cover them. A row assessed under an
older mapping deliberately keeps that assessment: it is what the caseworker saw and annotated.
The internal mapper treats a version it does not know as "no assessment", which covers the
window where the API is deployed ahead of the internal app. Changing the tool schema's shape
means bumping `analysis_schema_version` in `main.tf`. The field names in
`statement_details` and `category_remarks` are read by name in the normaliser; there is no
automated check that the two agree, so change them together.
