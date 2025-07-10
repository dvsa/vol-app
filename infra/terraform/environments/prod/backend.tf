terraform {
  backend "s3" {
    bucket         = "vol-app-146997448015-terraform-state"
    dynamodb_table = "vol-app-146997448015-prod-terraform-state-lock"
    encrypt        = true
    key            = "prod.tfstate"
    region         = "eu-west-1"
  }
}
