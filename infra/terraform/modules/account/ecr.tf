module "ecr" {
  source  = "terraform-aws-modules/ecr/aws"
  version = "~> 1.6"

  repository_name = "vol-app"

  repository_read_access_arns       = var.ecr_read_access_arns
  repository_read_write_access_arns = var.ecr_read_write_access_arns

  create_lifecycle_policy           = true
  repository_lifecycle_policy = jsonencode({
    rules = [
      {
        rulePriority = 10,
        description  = "Keep last 5 release images",
        selection = {
          tagStatus     = "tagged",
          tagPrefixList = ["v"],
          countType     = "imageCountMoreThan",
          countNumber   = 5
        },
        action = {
          type = "expire"
        }
      },
      {
        rulePriority = 20,
        description  = "Keep last 5 non-release images",
        selection = {
          tagStatus     = "tagged",
          tagPrefixList = ["rc"],
          countType     = "imageCountMoreThan",
          countNumber   = 5
        },
        action = {
          type = "expire"
        }
      },
    ]
  })

  manage_registry_scanning_configuration = true
  registry_scan_type                     = "ENHANCED"
  registry_scan_rules = [
    {
      scan_frequency = "SCAN_ON_PUSH"
      filter         = "*"
      filter_type    = "WILDCARD"
    }, {
      scan_frequency = "CONTINUOUS_SCAN"
      filter         = "v*"
      filter_type    = "WILDCARD"
    }
  ]

  repository_force_delete = true
}
