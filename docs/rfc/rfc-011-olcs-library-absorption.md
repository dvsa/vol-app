# RFC-011: Absorb the OLCS PHP Libraries into the Mono-repository

## Summary

Bring the six OLCS PHP libraries whose only consumer is vol-app — `olcs-xmltools`, `olcs-logging`, `olcs-utils`, `olcs-transfer`, `olcs-common`, `olcs-auth` — into this repository. Five live as in-tree Composer packages under `lib/<name>`, consumed by the apps through relative path repositories; `olcs-xmltools` (API-only) is fully inlined into `app/api/module/XmlTools`. Full git history is preserved via subtree merges. The standalone GitHub repositories are archived and the Packagist packages marked abandoned once absorbed.

> This RFC is published retrospectively: the absorption is complete on `main`. It records the decisions taken and why.

## Problem

RFC-001 merged the three applications into this mono-repository but left the shared libraries as external Composer packages on Packagist. Those libraries have **no consumer other than vol-app**, yet being external imposed a continuous tax:

- **Cross-cutting changes were multi-repo PR trains.** A change touching a library and the apps meant: library PR → review → tag/release → per-app constraint-bump PRs → re-review. Slow feedback, easy to mis-sequence, and rollback required coordinated version pinning.
- **Local development was error-prone.** Testing a library change locally meant manually copying sibling repo checkouts into each app's `vendor/olcs/*` directory (or maintaining `compose-custom.yaml` mount overrides) — an easy-to-forget, easy-to-get-wrong workflow that regularly cost debugging time.
- **Duplicated overhead per repository.** Six sets of GitHub Actions workflows, Dependabot configs, branch protections and access control, for code that ships only inside vol-app.
- **Version drift.** Library `main` could sit ahead of what the apps' lockfiles pinned for long periods, so "what is actually deployed" required cross-referencing several repositories.
- The libraries are nominally open source on Packagist, but there are no external consumers to serve.

## Proposal

Absorb all six libraries into vol-app, leaf-first (dependency order): `olcs-xmltools` (pilot) → `olcs-logging` → `olcs-utils` → `olcs-transfer` → `olcs-common` → `olcs-auth`. One PR per library.

**Out of scope:** `php-govuk-account` (genuinely shared with other DVSA services) and `olcs-etl` (on its own replacement path via Doctrine migrations).

### Placement: in-tree Composer packages under `lib/`, not full inlining

Five libraries keep their own `composer.json` (own dependencies, own require-dev, own pinned QA toolchain) at `lib/<name>`, and each consuming app references them via a **relative path repository** with `symlink: true`:

```json
{ "type": "path", "url": "../../lib/olcs-logging", "options": { "symlink": true } }
```

Why keep them as packages rather than merging their source into the apps:

- **Independently testable.** Each library runs its own `composer all` (tests + phpcs + psalm + phpstan) in CI, with its own tool versions. This decouples upgrades: a library can stay on an older PHPUnit while the apps move ahead, and vice versa — each migrates on its own schedule.
- **Dependency truth is preserved.** Each library still declares its own runtime dependencies, so the apps' dependency trees remain honest rather than absorbing a merged blob of requirements.
- **The absorption itself stays incremental.** Import a library, wire it, verify, merge — without simultaneously rewriting its test suite to the apps' conventions.

`olcs-xmltools` is the deliberate exception: it is consumed by the API only, so it was fully inlined into `app/api/module/XmlTools` (src + tests under the API's own PHPUnit and static-analysis config, its four transitive Laminas dependencies promoted to direct API requirements). The pilot confirmed why full inlining doesn't scale to the bigger shared libraries: it forces an immediate test-suite migration to the host app's (newer, stricter) toolchain — acceptable for 25 tests, not for thousands.

Two mechanical consequences of path repositories, applied per library:

- **Relative paths, not absolute.** Absolute path repositories broke under Docker (VOL-6099) because host paths don't exist in containers; relative paths keep the symlink valid anywhere the repo is checked out.
- **Explicit `version` fields.** A path repository is canonical and reports `dev-<branch>` by default, which fails other packages' semver constraints (e.g. `olcs-common` requires `olcs-logging: ^9.0`). Each absorbed library carries its last released version (e.g. `"version": "9.0.1"`) so inter-library constraints resolve; apps require the libs as `"*"`.

### History preservation: subtree merges, no squash

Each library was imported with `git subtree add` (equivalently: an `-s ours` merge + `read-tree --prefix`) **without `--squash`**, preserving full commit history — from 275 commits (xmltools) to ~13,500 (common). `git log`/`git blame` continue to work across the import boundary via the merge's second parent.

Corollary: **absorption PRs must be merged with a merge commit, not squashed.** A squash flattens the import into a single commit and orphans the imported history (this happened to the `olcs-logging` PR and was repaired by a later graph-only merge). Repository settings were changed to allow merge commits for these PRs.

### Operational consequences

The running cost of in-tree path packages, all applied as part of the absorption:

- **CI.** A reusable `php-lib.yaml` workflow runs each library's `composer all`; the `ci.yaml` orchestrator detects `lib/**` changes and both runs the library jobs and re-tests the dependent apps.
- **Production packaging.** The packaged app artefact ships only the app directory, where `vendor/olcs/*` are relative symlinks pointing outside it. The package step materialises each symlink into a real copy so artefacts are self-contained.
- **Local Docker.** Compose bind-mounts map each `lib/<name>` into the consuming containers' `vendor/olcs/<name>`, since the app-dir mount alone would leave the symlinks dangling.
- **Static analysis.** The apps' psalm configs add `../../lib` to both `projectFiles` (so library symbols are scanned and resolvable) and `ignoreFiles` (so library code is not re-analysed under each app's config).

The old workflows this replaces — vendor-copying sibling repos, `compose-custom.yaml` library mounts, `dev-branch as version` aliases — were removed as each library landed.

### Endgame

With all six absorbed: the standalone GitHub repositories get a pointer README ("moved to `dvsa/vol-app`") and are archived (read-only — history remains available in both places), and the Packagist packages are marked abandoned.

## Alternatives Considered

### Status quo (keep the libraries external)

The costs in the Problem section are the argument. With a single consumer there is no benefit purchasing them.

### Fully inline everything into the apps

Merging library source directly into app module trees (as done for xmltools) was rejected for the five shared libraries: `olcs-common`/`olcs-auth` are consumed by two apps (inlining duplicates them), and it forces each library's test suite onto the host app's QA toolchain in the same change — coupling absorption to major test-migration work. Collapsing `lib/` into the apps remains available as a deliberate future decision; it loses per-library isolated testing and should be weighed on its own merits, not done as part of the move.

### Git submodules

Still separate repositories with separate releases — none of the coordination cost goes away — plus well-known developer-experience friction (detached heads, forgotten `submodule update`). Rejected.

### Squash imports

Importing each library as a single commit would have been simpler mechanically but destroys history and blame for ~17,000 commits of still-actively-maintained code. Rejected; the subtree/no-squash requirement is cheap by comparison.

### Keep publishing to Packagist via subtree splits

Maintaining read-only split mirrors would preserve the external packages, but there are no external consumers to serve — pure ongoing overhead. Archival + abandonment is honest about the packages' status.

## Follow-ups

- **Library lockfile policy.** The `lib/` packages have no committed `composer.lock`, so library CI resolves the latest versions within constraints while the apps test against their locked versions — a new upstream release can fail a library's CI for a version production isn't running (this has happened once, via a laminas-session minor). Options: commit per-library locks, pin to the apps' resolved versions, or keep "test against latest" with per-case test hardening (the current approach). To be decided as a cross-library policy.
- `compose-custom.yaml.dist` no longer carries library overrides and is a candidate for slimming once local-refresh tooling no longer depends on it.

## References

- RFC-001: Mono-repository (the applications)
- VOL-6099 — the absolute-path repository/Docker symlink failure that motivated relative paths
- Absorption tickets: VOL-7253 (xmltools), VOL-7254 (logging), VOL-7255 (utils), VOL-7256 (transfer), VOL-7257 (common), VOL-7258 (auth), VOL-7259 (docs)
