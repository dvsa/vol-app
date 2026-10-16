terraform {
  backend "s3" {
    bucket       = "vol-app-054614622558-terraform-state"
    encrypt      = true
    key          = "account.tfstate"
    region       = "eu-west-1"
    use_lockfile = true
  }
}
