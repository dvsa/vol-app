terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "< 6.32.2"
    }
    null = {
      source  = "hashicorp/null"
      version = "~> 3.2"
    }
  }

  required_version = ">= 1.0"
}

provider "aws" {
  region = "eu-west-1"

  allowed_account_ids = ["054614622558"]

  default_tags {
    tags = {
      Environment = "dev"
      Repository  = "https://github.com/dvsa/vol-app"
    }
  }
}
