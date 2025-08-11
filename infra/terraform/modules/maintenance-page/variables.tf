variable "environment" {
  type        = string
  description = "The environment to deploy to"
}

variable "domain_name" {
  type        = string
  description = "The domain name for the environment"
}

variable "route53_zone_id" {
  type        = string
  description = "Route53 zone ID for DNS records"
}
