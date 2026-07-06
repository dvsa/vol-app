terraform {
  backend "s3" {
    bucket       = "vol-app-054614622558-terraform-state"
    use_lockfile = true
    encrypt      = true
    key          = "dev.tfstate"
    region       = "eu-west-1"
  }
}
