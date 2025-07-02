---
sidebar_position: 10
title: PDF Converter Service (Interim Solution)
---

# PDF Converter Service (Interim Solution)

## Overview

This document describes the interim PDF conversion solution implemented as part of the [VOL-1571 "Renderer 2.0"](https://dvsa.atlassian.net/browse/VOL-1571) epic. The solution replaces the legacy Windows EC2-based PDF conversion service with a containerized alternative running on ECS, serving as a stepping stone toward the full serverless implementation outlined in the epic.

## Background

### Legacy System Issues

The current PDF conversion system has several critical issues:

- **Outdated Infrastructure**: Windows servers running MS Office, unchanged since 2017
- **High Costs**: Needs windows and Office licences
- **Security Vulnerabilities**: Multiple issues identified during ITHC assessment
- **Maintenance Burden**: Windows servers require regular patching and maintenance

### Epic Goal

The VOL-1571 epic aims to build "Renderer 2.0" - a secure, on-demand PDF generation service with lower maintenance requirements that aligns with DVSA's serverless-first strategy.

## Why Not Lambda (Yet)?

While the ultimate goal is a serverless Lambda-based solution, the current reliance on RTF templates makes this impractical.

### The RTF Template Challenge

The VOL system uses dozens of RTF templates for official letters and documents, all designed in Microsoft Word. These must render with high fidelity to maintain legal compliance and agency consistency.

**The Reality**: While JavaScript libraries that convert RTF to PDF exist (like rtf.js), they have significant compromises - especially in how closely their output matches what was originally designed in MS Word. For official government correspondence, even minor differences in layout, spacing, or formatting are unacceptable.

### Why Gotenberg?

Gotenberg packages LibreOffice, which provides the closest match to MS Word's RTF rendering outside of Microsoft's own products. This ensures:

- Templates render as originally designed
- No unexpected formatting changes in official letters
- Drop-in replacement for the Windows service

Until all RTF templates are migrated to HTML (This will start soon but will be an ongoing effort), Gotenberg provides the most reliable path forward while eliminating Windows server dependencies.

## Interim Solution: Gotenberg

[Gotenberg](https://gotenberg.dev/) provides a containerized API for document conversion, including LibreOffice support for RTF files.

### Key Benefits

- **Local Stack Enhancement**: Allows devs to test VOL with the same rendering engine used in prod/nonpod
- **Drop-in Replacement**: A new simple PHP client consumes the Gotenberg API or falls back to old Renderer
- **Containerized**: Runs on ECS instead of Windows EC2
- **No License Costs**: Open source, no MS Office licenses required
- **Production Ready**: Battle-tested solution used by many organizations already

## Technical Implementation

### Architecture

**Local Development**: Gotenberg runs as a Docker container on port 3000, exposed locally on port 8080. The PHP API connects to `http://pdf-converter:3000` within the Docker network.

**ECS Production**: Gotenberg runs as an ECS service with 1GB CPU/2GB memory. The API service connects via the internal ECS service discovery DNS name. Both services share the same VPC and security groups for internal communication.

### PHP Components

1. **ConvertToPdfInterface** - Common interface for both implementations
2. **WebServiceClient** - Legacy Windows service adapter if configured for EC2 Renderer
3. **GotenbergClient** - Gotenberg service adapter if configured for ECS Gotenberg
4. **ConvertToPdfFactory** - Selects implementation based on configuration value

### Configuration

#### Local Development (compose.yaml)

```yaml
pdf-converter:
    image: gotenberg/gotenberg:8
    ports:
        - "8080:3000"
    environment:
        VIRTUAL_HOST: renderer.local.olcs.dev-dvsacloud.uk
        VIRTUAL_PORT: 3000
```

#### API Configuration (local.php)

```php
'convert_to_pdf' => [
    'type' => 'gotenberg',  // or 'webservice' for legacy
    'uri' => 'http://pdf-converter:3000',
],
```

#### ECS Service Configuration

```hcl
"pdf-converter" = {
  cpu        = 1024
  memory     = 2048
  version    = "8"
  repository = "docker.io/gotenberg/gotenberg"
  # ... other ECS configuration
}
```

## Migration Path

### Phase 1: Current Implementation ✓

- Gotenberg on ECS for all PDF conversions
- **Critical**: Maintains exact RTF rendering fidelity we are used to with windows Renderer Service
- Removes Windows server dependency and MS Office dependency
- Zero impact on document output quality - Have tested half a dozen and all look identical so far

### Phase 2: Dual Rendering System (Future)

- Implement Lambda function for NEW HTML-based documents
- Gotenberg continues handling ALL RTF templates
- Gradual introduction of HTML templates for new document types
- Both systems run in parallel

### Phase 3: Progressive RTF Migration (Long-term)

- Template-by-template conversion from RTF to HTML
- Each conversion requires:
    - Template redesign in HTML/CSS
    - Extensive testing for layout accuracy
    - Stakeholder sign-off on formatting changes
    - Update to document generation code
- **Reality**: This phase will take years due to the volume of templates

### Phase 4: Full Serverless (Eventual)

- Only achievable after ALL RTF templates are retired
- Lambda handles all conversions
- Gotenberg decommissioned
- Complete "Renderer 2.0" implementation

**Important Note**: Gotenberg will remain essential until the last RTF template is migrated. Given the business-critical nature of these documents and the effort required for migration, this interim solution will likely be in production for several years.

## Benefits Delivered

This interim solution provides immediate value while progressing toward the epic's goals:

### Cost Savings

- Eliminates Windows server costs
- Removes MS Office licensing requirements
- Reduces infrastructure maintenance overhead

### Security Improvements

- Addresses ITHC-identified vulnerabilities
- Removes outdated Windows servers
- Implements modern containerized architecture

### Operational Benefits

- Improved reliability and uptime
- Easier maintenance and updates
- Better monitoring and logging

### Strategic Alignment

- Moves toward serverless-first architecture
- Enables gradual migration path
- Maintains service continuity

## Summary

This interim Gotenberg solution represents a pragmatic step toward the full "Renderer 2.0" vision. It immediately addresses critical security and cost concerns while maintaining full compatibility with existing RTF-based document generation. The phased approach ensures service continuity while enabling progressive modernization toward a fully serverless architecture.
