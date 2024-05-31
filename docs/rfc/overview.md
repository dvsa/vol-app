# Overview

This document outlines the Request For Comments (RFC) process, designed to ensure thorough consideration and team consensus on proposed changes.

## Purpose

The RFC process serves as a structured avenue for proposing, discussing, and refining alterations within this repository, fostering collaboration and informed decision-making.

## How it Works

The RFC process operates as follows:

1. **Proposal Creation**: Propose a new RFC by creating a new file named `rfc-[number]-[title]` in this directory. It is _recommended_ to use the [template](#template) below for file structure.
2. **Pull Request**: Submit the RFC via a pull request.
3. **Discussion and Iterative Refinement**: Engage in discussions and iterate on the RFC as needed using pull request comments. Allow a minimum of 7 days for open discussion and feedback on the RFC.
4. **Acceptance**: Upon consensus, merge the RFC into the repository. If the RFC is rejected, close the pull request and provide a summary of the decision.

This structured approach ensures transparency, thorough deliberation, and alignment before implementing changes.

:::info

If no objections are raised within 7 days, the RFC will be considered accepted. If objections are raised, the RFC will be revised and resubmitted for further discussion.

If no consensus is reached, the lead engineer will make the final decision.

:::

## Template

```markdown
# RFC-000: Title

## Summary

<!-- Summarise the RFC in a few sentences. -->

## Problem

<!-- What is the problem that this RFC is trying to solve? -->

## Proposal

<!-- What is the proposed solution? -->
```
