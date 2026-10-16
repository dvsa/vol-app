terraform {
  backend "s3" {
    bucket       = "vol-app-146997448015-terraform-state"
    encrypt      = true
    key          = "account.tfstate"
    region       = "eu-west-1"
    use_lockfile = true
  }
}
