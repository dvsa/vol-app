# RFC-006: Making Docker images more accessible

## Summary

This RFC proposes storing application images in a publicly accessible Docker registry in addition to the existing private Elastic Container Registry (ECR).

## Problem

### Accessibility

Accessing images in private repositories requires authentication and appropriate IAM permissions. This introduces additional steps required for onboarding/offboarding, deployment, testing, and local development.

These images do not contain sensitive information and the intention is to make the code that builds these images, open-source.

### Automated version maintenance

Dependabot will require an AWS access key and secret to be able to determine the latest versions in the private ECR.

## Proposal

During the CI/CD workflow we will push the images to the GitHub Container Registry (GHCR) alongside the existing push to the existing private ECR. This will allow us to continue to take advantage of ECR scanning and lifecycle policies while making the images more accessible.

GHCR will only be used as a mirror for the images in the private ECR and will not be used in the deployment process.

GHCR is [free](https://docs.github.com/en/billing/managing-billing-for-github-packages/about-billing-for-github-packages) for public repositories and has a generous storage limit.

## Other options considered

### Changing the ECR to public

[ECR image scanning](https://github.com/aws/containers-roadmap/issues/2208) & [lifecycle policies](https://github.com/aws/containers-roadmap/issues/1268) are not available for public ECR repositories at the moment and both deemed important features at this time.

### Using a pull-through cache from the private ECR in a public ECR

[ECR to ECR pull through cache is not supported yet.](https://github.com/aws/containers-roadmap/issues/2208)

### Pushing to a Public ECR in addition to the private ECR

This offers the same benefits as pushing to GHCR but pushing to GHCR improves visibility on the GitHub UI and removes the need to setup an IAM permission allowing the GitHub Actions runner to push images the public ECR.
