data "aws_kms_alias" "ssm" {
  name = "alias/aws/ssm"
}

# The upstream terraform-aws-modules/ssm-parameter module still renders
# `insecure_value` for SecureString parameters, which AWS provider v6 rejects.
# Manage these parameters directly until that module is fixed upstream.
resource "aws_ssm_parameter" "application_parameters" {
  for_each = var.application_parameters

  name   = "/applicationparams/${var.environment}/${each.key}"
  type   = "SecureString"
  value  = each.value
  key_id = data.aws_kms_alias.ssm.target_key_arn
}
