// Dependencies: only the AWS SDK v3 bundled with the Lambda Node.js runtime
// (@aws-sdk/client-s3 and @aws-sdk/client-bedrock-runtime). No third-party deps.
//
// Lambda event:
//   { "bucket": "...", "key": "...", "modelId"?: "...", "maxBytes"?: 14680064 }
// Output: classification object ready for the SM to tag + emit, e.g.
//   { classification, classificationConfidence, totalPages, classifiedPages,
//     dominantTypePages, pageBreakdown, reasoning, modelId }

import { S3Client, GetObjectCommand, HeadObjectCommand } from "@aws-sdk/client-s3";
import { BedrockRuntimeClient, InvokeModelCommand } from "@aws-sdk/client-bedrock-runtime";

// --- Configuration ------------------------------------------------------------

// MODEL_ID is injected by Terraform as a cross-region inference profile ID,
// e.g. "eu.anthropic.claude-haiku-4-5-20251001-v1:0". The fallback is only
// reached if the Lambda is invoked without the env var set (e.g. local testing).
const DEFAULT_MODEL_ID = process.env.MODEL_ID || "eu.anthropic.claude-haiku-4-5-20251001-v1:0";

// Bedrock caps a request payload at 20 MB and base64 inflates bytes by ~33%, so
// the raw PDF must stay under ~14 MB to be sent inline. Larger files are NOT
// classified here - they should route to manual review or a pre-split step.
const DEFAULT_MAX_BYTES = Number(process.env.MAX_BYTES || 14 * 1024 * 1024);

const DOCUMENT_TYPES = [
  {
    key: "BANK_STATEMENT",
    description:
      "A statement issued by a bank or building society: opening/closing balances, dated debits and credits, a running balance, bank letterhead, (often masked) account number and sort code, and a statement period. Personal or business. NOT a one-off receipt or an internal ledger export.",
  },
  {
    key: "TRANSACTION_REPORT",
    description:
      "A listing of financial transactions that is NOT a formal bank statement - an accounting-software export, payment-processor settlement report, online-banking download, or card-machine takings report. Tabular (date, description, amount, maybe category/reference); lacks bank letterhead or opening/closing-balance framing, or comes from a third-party platform.",
  },
  {
    key: "NEWSPAPER_ADVERT",
    description:
      "A scanned/photographed newspaper or trade-publication advertisement - the statutory public notice of an operator licence application. Cues: masthead or page furniture, columnar print, a dated publication header, advert body naming the operator and application. May be a clipping.",
  },
];

const FALLBACK_TYPE = "UNCLASSIFIED";

// Forced-tool schema. The `type` enum is injected from DOCUMENT_TYPES at
// request time so the model can only emit valid keys.
const TOOL_NAME = "submit_classification";

function buildSystemPrompt() {
  const typeList = DOCUMENT_TYPES.map((t) => `- ${t.key}: ${t.description}`).join("\n");
  return [
    "You are a document classifier for the DVSA Office of the Traffic Commissioner operator-licensing pipeline.",
    "You are given the rendered pages of a single uploaded PDF, which may contain one document type or several stapled together.",
    "Classify each page into exactly one of these types:",
    typeList,
    `- ${FALLBACK_TYPE}: a page that does not clearly match any type above, or is unreadable.`,
    "",
    "Call the submit_classification tool with the per-type page breakdown and the total page count.",
    "Page counts must sum to the total number of pages. Do not compute a dominant type or confidence yourself - the pipeline does that from your counts.",
  ].join("\n");
}

function buildToolSchema() {
  const typeEnum = [...DOCUMENT_TYPES.map((t) => t.key), FALLBACK_TYPE];
  return {
    name: TOOL_NAME,
    description: "Report the per-page document-type breakdown for the uploaded PDF.",
    input_schema: {
      type: "object",
      required: ["pageBreakdown", "totalPages", "reasoning"],
      properties: {
        totalPages: { type: "integer", description: "Total pages in the document." },
        pageBreakdown: {
          type: "array",
          description: "One entry per distinct type present; pageCounts sum to totalPages.",
          items: {
            type: "object",
            required: ["type", "pageCount"],
            properties: {
              type: { type: "string", enum: typeEnum },
              pageCount: { type: "integer" },
            },
          },
        },
        reasoning: {
          type: "string",
          description: "One or two sentences on the cues that drove the classification.",
        },
      },
    },
  };
}

// --- Clients ------------------------------------------------------------------

const s3 = new S3Client({});
const bedrock = new BedrockRuntimeClient({
  region: process.env.BEDROCK_REGION || process.env.AWS_REGION || "eu-west-1",
});

// --- Core ---------------------------------------------------------------------

// Mirrors classification.asl.json ExtractClassification: UNCLASSIFIED pages are
// noise, so the dominant type is the largest CLASSIFIED type and confidence is
// dominantPages / classifiedPages.
function computeVerdict(pageBreakdown, modelTotalPages) {
  const totalPages = pageBreakdown.reduce((n, b) => n + b.pageCount, 0) || modelTotalPages || 0;

  const classified = pageBreakdown.filter((b) => b.type !== FALLBACK_TYPE);
  const classifiedPages = classified.reduce((n, b) => n + b.pageCount, 0);

  const sortedClassified = [...classified].sort((a, b) => b.pageCount - a.pageCount);
  const dominant = sortedClassified[0];
  const classification = dominant ? dominant.type : FALLBACK_TYPE;
  const dominantTypePages = dominant ? dominant.pageCount : 0;
  const classificationConfidence = classifiedPages > 0 ? dominantTypePages / classifiedPages : 0;

  return {
    classification,
    classificationConfidence,
    totalPages,
    classifiedPages,
    dominantTypePages,
    pageBreakdown: [...pageBreakdown].sort((a, b) => b.pageCount - a.pageCount),
  };
}

async function invokeClassifier(pdfBytes, modelId) {
  const body = {
    anthropic_version: "bedrock-2023-05-31",
    max_tokens: 1024,
    // temperature 0 for deterministic classification (valid on Haiku 4.5).
    temperature: 0,
    // cache_control is harmless if the system block is below the model's cache
    // minimum (4096 tokens on Haiku 4.5); it starts paying off once the type
    // registry grows large enough to cross that threshold.
    system: [{ type: "text", text: buildSystemPrompt(), cache_control: { type: "ephemeral" } }],
    tools: [buildToolSchema()],
    tool_choice: { type: "tool", name: TOOL_NAME },
    messages: [
      {
        role: "user",
        content: [
          {
            type: "document",
            source: {
              type: "base64",
              media_type: "application/pdf",
              data: Buffer.from(pdfBytes).toString("base64"),
            },
          },
          { type: "text", text: "Classify this document using the submit_classification tool." },
        ],
      },
    ],
  };

  const resp = await bedrock.send(
    new InvokeModelCommand({
      modelId,
      contentType: "application/json",
      accept: "application/json",
      body: JSON.stringify(body),
    }),
  );

  const payload = JSON.parse(new TextDecoder().decode(resp.body));
  const toolUse = (payload.content || []).find((b) => b.type === "tool_use" && b.name === TOOL_NAME);
  if (!toolUse) {
    throw new Error(`Model did not return a ${TOOL_NAME} tool call (stop_reason=${payload.stop_reason})`);
  }
  return { input: toolUse.input, usage: payload.usage };
}

// Shared by the Lambda handler and the CLI shim.
export async function classify({ pdfBytes, modelId = DEFAULT_MODEL_ID }) {
  const { input, usage } = await invokeClassifier(pdfBytes, modelId);
  const verdict = computeVerdict(input.pageBreakdown || [], input.totalPages);
  return { ...verdict, reasoning: input.reasoning, modelId, usage };
}

// --- Lambda handler -----------------------------------------------------------

export const handler = async (event) => {
  const { bucket, key, modelId = DEFAULT_MODEL_ID, maxBytes = DEFAULT_MAX_BYTES } = event ?? {};
  if (!bucket || !key) {
    throw new Error("classify-document requires `bucket` and `key` in the event payload");
  }

  // Pre-flight: refuse documents too large to send inline. The caller's Catch
  // routes this to DocumentProcessing-ClassificationFailed (manual review).
  const head = await s3.send(new HeadObjectCommand({ Bucket: bucket, Key: key }));
  if (head.ContentLength > maxBytes) {
    const err = new Error(`Document ${head.ContentLength} bytes exceeds inline-classification limit ${maxBytes} bytes`);
    err.name = "DocumentTooLargeForInlineClassification";
    throw err;
  }

  const obj = await s3.send(new GetObjectCommand({ Bucket: bucket, Key: key }));
  const pdfBytes = await obj.Body.transformToByteArray();

  return classify({ pdfBytes, modelId });
};

// --- CLI shim (local testing) -------------------------------------------------
// Run against a local PDF:  node index.mjs ./sample.pdf
// (Uses your ambient AWS credentials + region for the Bedrock call.)
if (import.meta.url === `file://${process.argv[1]}`) {
  const path = process.argv[2];
  if (!path) {
    console.error("usage: node index.mjs <local-pdf-path>");
    process.exit(1);
  }
  const { readFile } = await import("node:fs/promises");
  const pdfBytes = await readFile(path);
  const result = await classify({ pdfBytes });
  console.log(JSON.stringify(result, null, 2));
}
