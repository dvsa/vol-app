module "application_paramters" {
  source   = "terraform-aws-modules/ssm-parameter/aws"
  version  = ">= 2.1.1"
  for_each = var.application_parameters

  name  = "/applicationparams/${var.environment}/${each.key}"
  value = each.value
}