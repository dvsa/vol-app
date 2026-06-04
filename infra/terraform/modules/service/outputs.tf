output "api_tasks_iam_role_arn" {
  description = "IAM role ARN for the api ECS task role"
  value       = module.ecs_service["api"].tasks_iam_role_arn
}