terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.69.0"
    }
  }

  required_version = ">= 1.0"
}

provider "aws" {
  region = "eu-west-1"

  allowed_account_ids = ["054614622558"]

  default_tags {
    tags = {
      Environment = "int"
      Repository  = "https://github.com/dvsa/vol-app"
    }
  }
}
