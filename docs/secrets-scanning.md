# Secrets scanning

We scan for accidentally-committed secrets in two places:

1. **Locally** — `git-secrets` pre-commit hook (via husky).
2. **In CI** — shared `dvsa/.github` reusable workflow.

Standardised on [`awslabs/git-secrets`](https://github.com/awslabs/git-secrets)
with the `--register-aws` pattern set, matching the convention used
across DVSA.

## Install

    brew install git-secrets                  # macOS
    # https://github.com/awslabs/git-secrets#installing-git-secrets

After cloning vol-app, `npm install` registers the husky pre-commit
hook automatically. The hook scans staged content per commit. If
git-secrets isn't on your machine the hook prints a hint and skips —
CI is the authoritative gate.

## Allow-listing false positives

Add a regex per line to `.gitallowed` at the repo root; git-secrets
reads it automatically. Prefer this over `git config --add
secrets.allowed` so the rest of the team gets the same rule.

## When a real secret slips through

1. **Rotate the credential first** — assume it has been read.
2. Open a security ticket.
3. Purge from history with `git filter-repo`. Coordinate with
   anyone holding open branches before force-pushing.
