# RFC-006: Making Docker images more accessible

## Summary

This RFC proposes storing application images in a publicly accessible Docker registry in addition to the existing private Elastic Container Registry (ECR).

## Problem

### Accessibility

Accessing images in private repositories requires authentication and appropriate IAM permissions. This introduces additional steps required for onboarding/offboarding, deployment, testing, and local development. The images don't contain sensitive information, and the aim is to open-source the code responsible for building them.

### Automated version maintenance

Dependabot will require an AWS access key and secret to be able to determine the latest versions in the private ECR.

## Proposal

During the CI/CD workflow, we'll push images to both the GitHub Container Registry (GHCR) and the private ECR. This maintains the advantages of ECR scanning and lifecycle policies while improving image accessibility.

GHCR serves as a mirror for private ECR images and won't be involved in the deployment process.

GHCR is [free](https://docs.github.com/en/billing/managing-billing-for-github-packages/about-billing-for-github-packages) for public repositories and has a generous storage limit.

## Other options considered

### Changing the ECR to public

Public ECR repositories currently lack important features like [ECR image scanning](https://github.com/aws/containers-roadmap/issues/2208) and [lifecycle policies](https://github.com/aws/containers-roadmap/issues/1268).

### Using a pull-through cache from the private ECR in a public ECR

Unfortunately, [ECR to ECR pull-through caching isn't supported yet](https://github.com/aws/containers-roadmap/issues/2208).

### Pushing to a Public ECR in addition to the private ECR

While this provides similar benefits, pushing to GHCR enhances visibility on the GitHub UI and eliminates the need for an IAM permission setup for GitHub Actions runners to push images to the public ECR.
