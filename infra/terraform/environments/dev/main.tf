locals {
  service_names = ["api", "selfserve", "internal"]

  legacy_service_names = ["API", "IUWEB", "SSWEB"]
}

data "aws_ecr_repository" "this" {
  for_each = toset(local.service_names)

  name = "vol-app/${each.key}"
}

data "aws_security_group" "this" {
  for_each = toset(local.legacy_service_names)

  name = "DEV/APP/DA-OLCS-PRI-${each.key}-SG"
}

data "aws_subnets" "this" {
  for_each = toset(local.legacy_service_names)

  filter {
    name = "tag:Name"
    values = [
      "DEV/APP/DA-OLCS-PRI-${each.key}-1A",
      "DEV/APP/DA-OLCS-PRI-${each.key}-1B",
      "DEV/APP/DA-OLCS-PRI-${each.key}-1C"
    ]
  }
}

module "service" {
  source = "../../modules/service"

  environment = "dev"

  domain_name = "dev.olcs.dev-dvsacloud.uk"

  services = {
    "api" = {
      cpu    = 1024
      memory = 4096

      image = "${data.aws_ecr_repository.this["api"].repository_url}:${var.api_image_tag}"

      subnet_ids = data.aws_subnets.this["API"].ids

      security_group_ids = [
        data.aws_security_group.this["API"].id
      ]
    }
  }
}

