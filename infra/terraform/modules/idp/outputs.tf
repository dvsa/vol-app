output "documents_bucket_name" {
  description = "Name of the S3 bucket where documents are uploaded. Classification triggers on Object Created events from this bucket. When GuardDuty is added, this bucket becomes the upload target and a separate clean-documents bucket will feed Classification."
  value       = aws_s3_bucket.documents.bucket
}

output "documents_bucket_arn" {
  description = "ARN of the documents S3 bucket"
  value       = aws_s3_bucket.documents.arn
}

output "classify_document_lambda_arn" {
  description = "ARN of the classify-document Lambda"
  value       = aws_lambda_function.classify_document.arn
}

output "classification_sm_arn" {
  description = "ARN of the Classification Step Functions state machine"
  value       = aws_sfn_state_machine.classification.arn
}

output "classification_sm_name" {
  description = "Name of the Classification Step Functions state machine"
  value       = aws_sfn_state_machine.classification.name
}
