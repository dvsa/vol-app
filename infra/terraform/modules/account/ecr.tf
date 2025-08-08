locals {
  repositories = var.create_ecr_resources ? ["api", "cli", "selfserve", "internal", "liquibase", "search", "mock-govuk-signin"] : []
}

module "ecr" {
  for_each = toset(local.repositories)

  source  = "terraform-aws-modules/ecr/aws"
  version = "~> 2.2"

  repository_name = "vol-app/${each.key}"

  repository_read_access_arns = concat(
    [
      module.github[0].oidc_readonly_role_arn,
    ],
    var.ecr_read_access_arns
  )

  repository_read_write_access_arns = concat(
    [
      module.github[0].oidc_role_arn,
    ],
    var.ecr_read_write_access_arns
  )

  repository_image_tag_mutability = (each.key == "liquibase" || each.key == "mock-govuk-signin" ? "MUTABLE" : "IMMUTABLE")

  create_lifecycle_policy = true
  repository_lifecycle_policy = jsonencode({
    rules = [
      {
        rulePriority = 10,
        description  = "Keep last 20 release images",
        selection = {
          tagStatus      = "tagged",
          tagPatternList = ["*.*.*"],
          countType      = "imageCountMoreThan",
          countNumber    = 20
        },
        action = {
          type = "expire"
        }
      },
      {
        rulePriority = 20,
        description  = "Keep last 20 non-release images",
        selection = {
          tagStatus      = "tagged",
          tagPatternList = ["*"],
          countType      = "imageCountMoreThan",
          countNumber    = 20
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
      filter = [
        {
          filter      = "*",
          filter_type = "WILDCARD"
        }
      ],
    },
    {
      scan_frequency = "CONTINUOUS_SCAN"
      filter = [
        {
          filter      = "*.*.*",
          filter_type = "WILDCARD"
        },
      ],
    },
  ]
}

resource "aws_signer_signing_profile" "this" {
  platform_id = "Notation-OCI-SHA384-ECDSA"

  name_prefix = "vol_app_"
}

# Allow Lambda service to pull mock-govuk-signin images
resource "aws_ecr_repository_policy" "mock_govuk_signin_lambda" {
  count = contains(local.repositories, "mock-govuk-signin") ? 1 : 0

  repository = module.ecr["mock-govuk-signin"].repository_name

  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Sid    = "AllowLambdaPull"
        Effect = "Allow"
        Principal = {
          Service = "lambda.amazonaws.com"
        }
        Action = [
          "ecr:GetDownloadUrlForLayer",
          "ecr:BatchGetImage",
          "ecr:BatchCheckLayerAvailability"
        ]
      }
    ]
  })
}
