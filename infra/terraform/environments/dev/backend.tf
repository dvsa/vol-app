terraform {
  backend "s3" {
    bucket         = "vol-app-054614622558-terraform-state"
    dynamodb_table = "vol-app-054614622558-dev-terraform-state-lock"
    encrypt        = true
    key            = "dev.tfstate"
    region         = "eu-west-1"
  }
}
