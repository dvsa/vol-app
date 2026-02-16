terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "< 6.32.2"
    }
    null = {
      source  = "hashicorp/null"
      version = ">= 3.0.0"
    }
  }

  required_version = ">= 1.0"
}

provider "aws" {
  region = "eu-west-1"

  allowed_account_ids = ["146997448015"]

  default_tags {
    tags = {
      Environment = "prod"
      Repository  = "https://github.com/dvsa/vol-app"
    }
  }
}
