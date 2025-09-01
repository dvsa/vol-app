# PrintPit

Virtual print server for VOL that captures print jobs as PDFs. Replaces EC2-based CUPS servers in non-production environments.

## Quick Start

```bash
# Local development
docker compose up -d printpit

# Access FileBrowser UI
http://localhost:8631

# Print jobs are sent internally to printpit:631
```

## Components

- **CUPS Server**: Accepts IPP print jobs on port 631
- **CUPS-PDF**: Converts print jobs to PDF files
- **FileBrowser**: Web UI and REST API for viewing PDFs
- **Supervisor**: Process management
- **Cleanup**: Auto-removes PDFs after 6 hours

## Documentation

See comprehensive documentation at [docs/infrastructure/docker/printpit.md](../../../docs/infrastructure/docker/printpit.md)

## Files

- `Dockerfile` - Alpine Linux container definition
- `cupsd.conf` - CUPS server configuration
- `cups-pdf.conf` - PDF printer settings
- `supervisord.conf` - Process management
- `entrypoint.sh` - Container initialization
- `cleanup.sh` - PDF cleanup script
