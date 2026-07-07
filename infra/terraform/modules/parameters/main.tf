module "application_paramters" {
  source   = "terraform-aws-modules/ssm-parameter/aws"
  for_each = var.application_parameters

  name  = "/applicationparams/${var.environment}/${each.key}"
  value = each.value
}