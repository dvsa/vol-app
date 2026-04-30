terraform {
  backend "s3" {
    bucket       = "vol-app-146997448015-terraform-state"
    use_lockfile = true
    encrypt      = true
    key          = "prep.tfstate"
    region       = "eu-west-1"
  }
}
