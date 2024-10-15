terraform {
  backend "s3" {
    bucket         = "vol-app-146997448015-terraform-state"
    dynamodb_table = "vol-app-146997448015-terraform-state-lock"
    encrypt        = true
    key            = "account.tfstate"
    region         = "eu-west-1"
  }
}
