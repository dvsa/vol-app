data "aws_kms_alias" "ssm" {
  name = "alias/aws/ssm"
}

module "application_paramters" {
  source  = "terraform-aws-modules/ssm-parameter/aws"
  version = ">= 2.1.1"

  for_each = var.application_parameters

  name        = "/applicationparams/${var.environment}/${each.key}"
  value       = each.value
  secure_type = true
  type        = "SecureString"
  key_id      = data.aws_kms_alias.ssm.target_key_arn
}