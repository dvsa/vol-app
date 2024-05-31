# RFC-003: Code Owners

## Introduction

To improve the ownership and accountability of our codebase, we propose assigning code owners to specific parts of the codebase. This initiative aims to streamline code reviews, maintain dependencies, and proactively identify potential issues.

## Problem

### Unmaintained Dependencies

Dependencies in repositories often remain unmaintained, requiring tickets to be created to gain visibility and address them.

### Code Reviews

Code reviews frequently stall awaiting reviewers, necessitating manual assignment of reviewers and chasing reviews on Slack.

### Early Problem Detection

Less volatile parts of the codebase are often overlooked, leading to issues being detected late in the development process.

## Proposal

To address these issues, we propose the introduction of code owners. Code owners are team members that are acting lead maintainer of a specific repository or part of the codebase.

They are responsible for:

-   Reviewing pull requests that affect their assigned code.
-   Keeping dependencies up to date (or creating tickets for them if they require large refactors).
-   Identifying improvements and creating tickets for them.
-   Release management for their assigned code (if applicable).

### Time Investment

Allocate roughly 5% of a developer's capacity each sprint (equivalent to one half-day per sprint) for code owner tasks. This can be done once a month, so a full day every two sprints to work on these tasks. _Sprints should be planned with this in mind; sprint work will take precedence over code-owner tasks._

### Coordination

The scrum events will be used to coordinate the code owner's activities:

-   **Sprint Planning**: Allocate time for code owner tasks.
-   **Daily Stand-ups**: Briefly discuss ongoing code owner activities.
-   **Retrospective**: Discuss the effectiveness of the code owner's activities and identify improvements.

A quarterly session will be held to rotate code ownership among team members. During the session, the code owners will summarize the work done in their respective areas over the previous quarter.

## Metrics for Success

-   Reduction in the number of unmaintained dependencies.
-   Decreased turnaround time for code reviews.
-   Early detection and resolution of issues in less volatile parts of the codebase.
