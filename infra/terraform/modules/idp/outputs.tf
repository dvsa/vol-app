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

output "extraction_sm_arn" {
  description = "ARN of the Extraction Step Functions state machine"
  value       = aws_sfn_state_machine.extraction.arn
}

output "extraction_sm_name" {
  description = "Name of the Extraction Step Functions state machine"
  value       = aws_sfn_state_machine.extraction.name
}

output "idp_output_bucket_name" {
  description = "Name of the S3 bucket used by BDA to write extraction results"
  value       = aws_s3_bucket.idp_output.bucket
}

output "bda_project_arn" {
  description = "ARN of the Bedrock Data Automation project"
  value       = awscc_bedrock_data_automation_project.idp.project_arn
}

output "bda_blueprint_arn" {
  description = "ARN of the bank statement BDA blueprint"
  value       = awscc_bedrock_blueprint.bank_statement.blueprint_arn
}

output "idp_output_bucket_arn" {
  description = "ARN of the BDA output S3 bucket"
  value       = aws_s3_bucket.idp_output.arn
}
