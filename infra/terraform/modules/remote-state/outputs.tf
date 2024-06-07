output "dynamodb_state_lock_policy_arn" {
  description = "The ARN of the IAM policy that allows DynamoDB access for state locking"
  value       = try(module.dynamodb_state_lock_policy[0].arn, null)
}

output "s3_state_policy_arn" {
  description = "The ARN of the IAM policy that allows S3 access for the state"
  value       = try(module.s3_state_policy.arn, null)
}

output "s3_state_readonly_policy_arn" {
  description = "The ARN of the IAM policy that allows S3 read-only access for the state"
  value       = try(module.s3_state_readonly_policy.arn, null)
}
