terraform {
  backend "s3" {
    bucket         = "vol-app-146997448015-terraform-state"
    dynamodb_table = "vol-app-146997448015-prep-terraform-state-lock"
    encrypt        = true
    key            = "prep.tfstate"
    region         = "eu-west-1"
  }
}
