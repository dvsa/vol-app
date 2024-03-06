# This imports the remote state so that it can be maintained by the account module.
module "account-remote-state" {
  source = "../../modules/remote-state"

  identifier = "vol-app"
}

import {
  to = module.account-remote-state.module.dynamodb_table.aws_dynamodb_table.this[0]
  id = "vol-app-054614622558-terraform-state-lock"
}

import {
  to = module.account-remote-state.module.s3[0].aws_s3_bucket.this[0]
  id = "vol-app-054614622558-terraform-state"
}

import {
  to = module.account-remote-state.module.s3[0].aws_s3_bucket_policy.this[0]
  id = "vol-app-054614622558-terraform-state"
}

import {
  to = module.account-remote-state.module.s3[0].aws_s3_bucket_lifecycle_configuration.this[0]
  id = "vol-app-054614622558-terraform-state"
}

import {
  to = module.account-remote-state.module.s3[0].aws_s3_bucket_ownership_controls.this[0]
  id = "vol-app-054614622558-terraform-state"
}

import {
  to = module.account-remote-state.module.s3[0].aws_s3_bucket_server_side_encryption_configuration.this[0]
  id = "vol-app-054614622558-terraform-state"
}

import {
  to = module.account-remote-state.module.s3[0].aws_s3_bucket_public_access_block.this[0]
  id = "vol-app-054614622558-terraform-state"
}

import {
  to = module.account-remote-state.module.s3[0].aws_s3_bucket_versioning.this[0]
  id = "vol-app-054614622558-terraform-state"
}

import {
  to = module.account-remote-state.module.dynamodb_state_lock_policy[0].aws_iam_policy.policy[0]
  id = "arn:aws:iam::054614622558:policy/vol-app-054614622558-terraform-state-lock-policy"
}

import {
  to = module.account-remote-state.module.s3_state_policy[0].aws_iam_policy.policy[0]
  id = "arn:aws:iam::054614622558:policy/vol-app-054614622558-terraform-state-policy"
}
