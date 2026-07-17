# ============================================================
# Bedrock Data Automation — Blueprint
#
# Custom DVSA bank statement extraction blueprint.
# Schema is in config/bank-statement-blueprint.json; edit the
# schema there and re-apply to update the blueprint in place.
#
# ============================================================
resource "awscc_bedrock_blueprint" "bank_statement" {
  blueprint_name = "${local.name_prefix}-bank-statement"
  type           = "DOCUMENT"
  schema         = file("${path.module}/config/bank-statement-blueprint.json")
}

# ============================================================
# Bedrock Data Automation — Project
#
# Wires the bank statement blueprint into a BDA project that
# the Extraction SM invokes via InvokeDataAutomationAsync.
#
# Key configuration choices (mirrors the CDK POC):
#   - Document granularity: DOCUMENT only (no page/word noise)
#   - Bounding box: DISABLED (coordinates not needed downstream)
#   - Output format: MARKDOWN only (plain text/HTML/CSV disabled)
#   - Additional file format: DISABLED (JSON only, no JSON+ side files)
#   - Generative field: DISABLED (no BDA-generated summaries)
#   - Splitter: ENABLED — BDA segments bundled PDFs; only segments
#     matching the blueprint get custom_output, lifting the 20-page
#     sync limit up to 3000 pages.
#   - Image / video / audio modalities: DISABLED — PDFs only.
# ============================================================
resource "awscc_bedrock_data_automation_project" "idp" {
  project_name        = "${local.name_prefix}-bda"
  project_description = "DVSA document processing project — custom bank statement blueprint for extraction, splitter enabled for bundled/mixed-content PDFs."

  custom_output_configuration = {
    blueprints = [
      {
        blueprint_arn = awscc_bedrock_blueprint.bank_statement.blueprint_arn
      }
    ]
  }

  standard_output_configuration = {
    document = {
      extraction = {
        granularity = {
          types = ["DOCUMENT"]
        }
        bounding_box = {
          state = "DISABLED"
        }
      }
      output_format = {
        text_format = {
          types = ["MARKDOWN"]
        }
        additional_file_format = {
          state = "DISABLED"
        }
      }
      generative_field = {
        state = "DISABLED"
      }
    }
  }

  override_configuration = {
    document = {
      splitter = {
        state = "ENABLED"
      }
    }
    image = {
      modality_processing = {
        state = "DISABLED"
      }
    }
    video = {
      modality_processing = {
        state = "DISABLED"
      }
    }
    audio = {
      modality_processing = {
        state = "DISABLED"
      }
    }
  }
}
