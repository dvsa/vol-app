variable "maintenance_domain" {
  type        = string
  description = "The full domain name for the maintenance page (e.g., maintenance.vol-app.test.dvsa.gov.uk)"
}

variable "route53_zone_id" {
  type        = string
  description = "Route53 zone ID for DNS records"
}

variable "github_oidc_subjects" {
  type        = list(string)
  description = "List of GitHub OIDC subjects allowed to assume the maintenance deployment role"
  default     = []
}
