module "assets" {
  count = var.create_assets_bucket ? 1 : 0

  source  = "terraform-aws-modules/s3-bucket/aws"
  version = "~> 4.0"

  bucket = "vol-app-assets"
}
