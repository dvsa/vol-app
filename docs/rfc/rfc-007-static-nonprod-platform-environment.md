# RFC-007: Static non-prod platform environmentq

## Summary

This RFC proposes having a static platform environment (possibly QA) to be used by the infrastructure release pipeline. This is to provide an alternative route for any substantial infrastructure changes to attempt to prevent breaking or impeding the container path to live while being proved.

## Problems

### Application container release pipeline

As per [rfc-005](https://github.com/dvsa/vol-app/blob/main/docs/rfc/rfc-005-add-terraform-to-mono-repository.md) the scope of terraform code deployed to the application repo was limited to prevent further Terraform dependency complexity. As the time of writing this has not been superseded by any future request for comment that amends this stance.

The existing pipeline fundamentally guides the deployment of containers to ECS clusters that sit upon the entirety of the VOL cloud infrastructure. Any change to just the container image (which as per current understanding will be a significant amount of the change requests going forward) will be efficient as per design. This means that container change will be ready for rapid promotion through the remaining three environment leading to decrease in release cadence.

### Platform based  changes and potential scope for disruption

Infrastructure change will in most cases be broader and may involve a greater degree of proving. As such any breaking change will break the path to live for all other change being promoted to production. This has the potential to impede the progress we have made towards decreasing the release cadence.

An additional static environment using containers will be cheap and will give the longer riskier changes a pathway to live, ensuring proving on the container based solution removing some of the risk of impeding container deployment and creating feature bottlenecks.

## Proposal

During the CI/CD workflow, once a change is merged to main we will deploy to an additional static non-prod environment in parallel to Int after proving in Dev.

This will improve our deployment resilience when working on long-running potentially breaking changes while still allowing us to test on the existing containerised solution (currently this is only deployed to one non-prod environment).

If this does not achieve the stated aim of testing long-running cloud changes on a representative environment then we will remove and apply directly to the Dev environment as per the container pipeline. This is accepting  in the vol-terraform pipelines and accept the potential for blocking the promotion route to live for all changes. As part of this we should also monitor the usage.

## Other options considered

### Deploying changes directly to Dev for any functional testing

This would allow us to test any functional capability along with the infrastructure change. It comes at the risk of delaying container change and preventing value being delivered as early as it could be from a delivery perspective. Given the reduced cost and complexity this container design is going to provide us it doesn't seem worth it.

### Deploying to ephemeral environments

Ephemeral environments won't allow us to effectively test the scope of a platform change in a single ephemeral environment. A network change (security group, Nacl etc.) break all ephemeral environments impeding all other container based change.
