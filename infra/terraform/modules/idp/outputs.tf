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

output "documents_key_prefix" {
  description = "The S3 key prefix the EventBridge rule watches. Needed as the S3 bucket contains many different directories which are redundant to IDP. Useful for confirming the active filter without reading Terraform state."
  value       = var.documents_key_prefix
}
