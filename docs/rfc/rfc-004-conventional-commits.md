# RFC-004: Conventional Commits

## Summary

Adopting conventional commits to enhance the readability and consistency of commit messages.

## Problem

Commit messages frequently lack readability and consistency, making them challenging to understand.

Automatically generating release notes becomes difficult without adhering to a consistent commit format.

## Proposal

Implement [conventional commits](https://www.conventionalcommits.org/en/v1.0.0/) to improve the readability of commit messages.

Initially, enforce conventional commit standards using a GitHub workflow. Over time, transition to using a GitHub ruleset for enforcement.
