terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 6.2.0"
    }
  }

  required_version = ">= 1.0"
}

provider "aws" {
  region = "eu-west-1"

  allowed_account_ids = ["054614622558"]

  default_tags {
    tags = {
      Repository = "https://github.com/dvsa/vol-app"
    }
  }
}

# ACM provider for CloudFront certificates (must be in us-east-1)
provider "aws" {
  alias = "acm"

  region = "us-east-1"

  allowed_account_ids = ["054614622558"]

  default_tags {
    tags = {
      Repository = "https://github.com/dvsa/vol-app"
    }
  }

  # Make it faster by skipping various bits not important.
  skip_metadata_api_check     = true
  skip_region_validation      = true
  skip_credentials_validation = true

  # skip_requesting_account_id should be disabled to generate valid ARN
  skip_requesting_account_id = false
}
