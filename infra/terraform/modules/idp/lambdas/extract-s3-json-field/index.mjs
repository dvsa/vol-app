// Reads a JSON object from S3 and returns either the whole parsed object or
// a sub-field reached by a dot-separated path. Exists to work around Step
// Functions' 256KB state-transition limit when an S3 JSON's full content
// exceeds that limit but the field we actually need is much smaller.

import { S3Client, GetObjectCommand } from "@aws-sdk/client-s3";

const s3 = new S3Client({});

export const handler = async (event) => {
  const { bucket, key, fieldPath } = event ?? {};

  if (!bucket || !key) {
    throw new Error("extract-s3-json-field requires `bucket` and `key` in the event payload");
  }

  const resp = await s3.send(new GetObjectCommand({ Bucket: bucket, Key: key }));
  const bodyText = await resp.Body.transformToString();
  const parsed = JSON.parse(bodyText);

  if (!fieldPath) {
    return parsed;
  }

  // Navigate a dot-separated path - e.g. "document.representation.text".
  // Stops cleanly at any null/undefined segment.
  const value = fieldPath.split(".").reduce((acc, segment) => (acc != null ? acc[segment] : undefined), parsed);

  if (value === undefined) {
    throw new Error(`Field not found at path '${fieldPath}' in s3://${bucket}/${key}`);
  }

  return value;
};
