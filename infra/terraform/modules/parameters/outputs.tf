output "dva_ni_export_s3uri" {
  description = "The value of the data-dva-ni-export-s3uri SSM parameter"
  value       = nonsensitive(aws_ssm_parameter.application_parameters["data-dva-ni-export-s3uri"].value)
}
