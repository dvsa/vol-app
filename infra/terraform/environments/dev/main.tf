locals {
  service_names = ["api", "selfserve", "internal"]

  legacy_service_names = ["API", "IUWEB", "SSWEB"]
}

data "aws_ecr_repository" "this" {
  for_each = toset(local.service_names)

  name = "vol-app/${each.key}"
}

data "aws_vpc" "this" {
  filter {
    name = "tag:Name"
    values = [
      "DEV/APP-VPC"
    ]
  }
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

data "aws_subnet" "this" {
  for_each = toset(flatten([
    for service_name, subnet_ids in data.aws_subnets.this : [
      for subnet_id in subnet_ids.ids : subnet_id
    ]
  ]))

  id = each.value

}

module "service" {
  source = "../../modules/service"

  environment = "dev"

  vpc_ids = data.aws_vpc.this.id

  vpc_azs = [
    "eu-west-1a",
    "eu-west-1b",
    "eu-west-1c"
  ]

  domain_name    = "dev.olcs.dev-dvsacloud.uk"
  assets_version = var.assets_version

  services = {
    "api" = {
      cpu    = 1024
      memory = 4096

      image = "${data.aws_ecr_repository.this["api"].repository_url}:${var.api_image_tag}"

      subnet_ids = data.aws_subnets.this["API"].ids

      access_point = "${var.api_image_tag}/data/cache"

      cidr_blocks = [
        for subnet in data.aws_subnet.this :
        subnet.cidr_block if contains(data.aws_subnets.this["API"].ids, subnet.id)
      ]

      security_group_ids = [
        data.aws_security_group.this["API"].id
      ]
    }

    "internal" = {
      cpu    = 1024
      memory = 4096

      image = "${data.aws_ecr_repository.this["internal"].repository_url}:${var.internal_image_tag}"

      subnet_ids = data.aws_subnets.this["IUWEB"].ids

      access_point = "${var.internal_image_tag}/data/cache"

      cidr_blocks = [
        for subnet in data.aws_subnet.this :
        subnet.cidr_block if contains(data.aws_subnets.this["IUWEB"].ids, subnet.id)
      ]

      security_group_ids = [
        data.aws_security_group.this["IUWEB"].id
      ]
    }

    "selfserve" = {
      cpu    = 1024
      memory = 4096

      image = "${data.aws_ecr_repository.this["selfserve"].repository_url}:${var.selfserve_image_tag}"

      subnet_ids = data.aws_subnets.this["SSWEB"].ids

      access_point = "${var.selfserve_image_tag}/data/cache"

      cidr_blocks = [
        for subnet in data.aws_subnet.this :
        subnet.cidr_block if contains(data.aws_subnets.this["SSWEB"].ids, subnet.id)
      ]

      security_group_ids = [
        data.aws_security_group.this["SSWEB"].id
      ]
    }
  }

}
