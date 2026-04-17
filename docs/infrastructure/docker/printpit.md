---
sidebar_position: 30
---

# PrintPit - Virtual Print Server

PrintPit is a containerized CUPS print server that can replace the EC2-based CUPS servers in non-production and PREP environments. It accepts print jobs and renders them as PDFs instead of sending them to physical printers, providing a lightweight, scalable solution for testing print functionality.

## Overview

PrintPit serves as a drop-in replacement for EC2 CUPS servers in testing environments, offering:

- **Full CUPS Compatibility**: Accepts print jobs via IPP protocol just like production CUPS servers. It actually IS running a CUPS server.
- **PDF Rendering**: All print jobs are saved as PDFs
- **Web Interface**: Devs and Testers can browse and download PDFs through FileBrowser UI
- **REST API**: Programmatic access for automated testing (VFT)
- **Auto-cleanup**: Configurable retention period for PDFs
- **Cost Efficiency**: Eliminates need for EC2 PRINT instances in non-production and PREP if desired

## Architecture

PrintPit replaces the traditional EC2-based CUPS infrastructure:

### Traditional Setup (Production)

```
VOL Application → EC2 CUPS Server → Physical Printers
```

### PrintPit Setup (Non-Production/PREP)

```
VOL Application → PrintPit Container → PDF Files → FileBrowser UI/API
```

## Deployment

PrintPit is deployed to ECS Fargate in non-production environments using a single hostname with different ports:

| Environment | CUPS Endpoint                            | FileBrowser UI                                    | Status |
| ----------- | ---------------------------------------- | ------------------------------------------------- | ------ |
| Dev         | `printpit.dev.olcs.dev-dvsacloud.uk:631` | `https://printpit.dev.olcs.dev-dvsacloud.uk:8631` | Active |
| Int         | `printpit.int.olcs.dev-dvsacloud.uk:631` | `https://printpit.int.olcs.dev-dvsacloud.uk:8631` | Active |
| QA          | `printpit.qa.olcs.dev-dvsacloud.uk:631`  | `https://printpit.qa.olcs.dev-dvsacloud.uk:8631`  | Active |

## Local Development

PrintPit is integrated into the main VOL Docker Compose setup and allows local print testing.

```bash
# Start PrintPit
docker compose up -d printpit

# Access FileBrowser UI
http://localhost:8631

# CUPS endpoint (internal to Docker network)
printpit:631
```

### Configuration

The API automatically connects to PrintPit in local development if using the local.php.dist suggested config.

## FileBrowser API

FileBrowser provides a REST API for programmatic access to PDFs, enabling automated testing scenarios including VFT integration.

### API Endpoints

All endpoints are unauthenticated when running with `--noauth` flag (this is test infrastructure. Auth is possible if desired).

#### List PDFs

```bash
GET https://printpit.{env}.olcs.dev-dvsacloud.uk:8631/api/resources/
```

Returns JSON array of all PDFs with metadata:

```json
{
    "items": [
        {
            "path": "No-Auth_Test__enable-job_1.pdf",
            "name": "No-Auth_Test__enable-job_1.pdf",
            "size": 3814,
            "extension": ".pdf",
            "modified": "2025-08-31T16:17:47.638222911+01:00",
            "mode": 438,
            "isDir": false,
            "isSymlink": false,
            "type": "pdf"
        }
    ],
    "numFiles": 1,
    "sorting": {
        "by": "name",
        "asc": false
    }
}
```

#### Download PDF

```bash
GET https://printpit.{env}.olcs.dev-dvsacloud.uk:8631/api/raw/job_001_document.pdf
```

Returns the PDF file directly for download or processing.

#### Delete PDF

```bash
DELETE https://printpit.{env}.olcs.dev-dvsacloud.uk:8631/api/resources/job_001_document.pdf
```

Removes the specified PDF from storage.

#### Search PDFs

```bash
GET https://printpit.{env}.olcs.dev-dvsacloud.uk:8631/api/search?query=licence
```

Search for PDFs by filename.

### Environment Variables

| Variable        | Default         | Description                         |
| --------------- | --------------- | ----------------------------------- |
| `CLEANUP_HOURS` | `6`             | Hours before automatic PDF deletion |
| `TZ`            | `Europe/London` | Container timezone                  |

## Building and Deployment

PrintPit uses a standalone GitHub Actions workflow for deployment:

```yaml
# Manual trigger with version
workflow_dispatch:
    inputs:
        version:
            description: "Version tag (e.g., 1.0.0)"
        push_to_prod:
            description: "Push to production ECR"
            type: boolean
```

### Build Process

1. Builds x86_64 architecture image
2. Tags with version and git SHA
3. Pushes to ECR and GHCR registries
4. Optionally promotes to production ECR

### Trigger Deployment

```bash
# Via GitHub UI
Navigate to Actions → PrintPit Workflow → Run workflow

# Via GitHub CLI
gh workflow run printpit.yaml -f version=1.0.0 -f push_to_prod=false
```
