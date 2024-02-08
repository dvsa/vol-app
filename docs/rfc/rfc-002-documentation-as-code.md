# RFC-002: Documentation-as/within-code

## Summary

Introduce documentation within the application codebase accessible via a web interface.

Depends on [RFC-001](./rfc-001-mono-repository.md).

## Problem

Traditional documentation stored in platforms like Confluence is often difficult to locate and maintain, frequently becoming outdated.

## Proposal

Documentation will be authored in Markdown and stored within the mono-repository (as proposed in RFC-001) under the `/docs` directory.

Additional tools proposed include:
- Utilizing the Docusaurus library to build the static site.
- Using GitHub Pages for serving the documentation.
- Employing GitHub Actions for deployment.
