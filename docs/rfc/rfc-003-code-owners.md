# RFC-003: Code Owners

## Summary

Introduce the concept of code owners to assign specific responsibilities for code within the team.

## Problem

### Unmaintained dependencies

Dependencies in repositories often remain unmaintained, requiring tickets to be created to gain visibility and address them.
### Code reviews

Code reviews frequently stall awaiting reviewers, necessitating manual assignment of reviewers.
### Early problem detection

Implementing code owners ensures that relevant team members are promptly notified of codebase changes, facilitating early involvement in the review process. Code owners can also participate in the design phase, aiding in the early detection of potential issues and proposing solutions.

## Proposal

Add a CODEOWNERS file to each repository maintained by the VOL team.

Allocate 5% of a developer's capacity each sprint (equivalent to 1 half day per sprint) for tasks such as updating dependencies, identifying improvements (and creating tickets), and general repository maintenance.

Organize a monthly session, led by the tech lead, to suggest improvements to the codebase. Code owners will lead this session.

Hold quarterly sessions, led by the tech lead, to rotate code ownership among team members.
