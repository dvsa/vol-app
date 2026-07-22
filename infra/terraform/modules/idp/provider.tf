terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = ">= 5.0.0"
    }
    archive = {
      source  = "hashicorp/archive"
      version = ">= 2.0.0"
    }
    awscc = {
      source  = "hashicorp/awscc"
      version = ">= 1.32.0"
    }
  }

  required_version = ">= 1.0"
}
