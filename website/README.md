# Documentation site

Builds the Markdown in [`docs/`](../docs) into the documentation site at https://dvsa.github.io/vol-app, using [docmd](https://docmd.io). CI builds it on pull requests that touch `docs/` or `website/`, and deploys it to GitHub Pages from `main` (`.github/workflows/deploy-documentation.yaml`).

## Running it locally

```bash
npm ci
npm start          # live-reloading preview at http://localhost:3000
npm run build      # the production site, written to build/
npm run validate   # fails on relative links to pages that don't exist, as CI does
```

`npm run serve` builds once and serves the result without live reload.

Install scripts are turned off in `.npmrc`. docmd's optional native engine would download a binary from a CDN, and the JavaScript engine it falls back to builds this site in seconds.

## Writing pages

Pages are plain Markdown. The sidebar is generated from the folder structure by `navigation.mjs`, using the same conventions the site had under Docusaurus:

- A page's sidebar label is its `sidebar_label` or `title` front matter, else its first `# ` heading, else its file name.
- `sidebar_position` front matter orders a page among its siblings. Pages without one come after, in file name order.
- A folder is a sidebar group. A `_category_.json` in it sets the group's `label`, `position` and `collapsed`. `collapsed: false` makes the group always open.
- A folder's `index.md` or `README.md` is the group's own page. `docs/README.md` is the home page.

The sidebar is worked out when the site starts building, so restart `npm start` for it to show a new page or a changed position.

Callouts use the same syntax as before: `:::note`, `:::tip`, `:::info`, `:::warning`, `:::danger` or `:::caution`, optionally followed by a title, and closed with `:::` on its own line. A callout can't contain a heading. `plugins/docusaurus-callouts.mjs` makes `:::tip` and titles work, which docmd handles differently. Fenced `mermaid` code blocks render as diagrams.

Link between pages with relative paths to the `.md` files, for example `[testing](./app/testing.md#why-ci-stays-serial)`. They work on GitHub and on the site.

## Configuration

`docmd.config.mjs` holds the site settings, with comments explaining each one. docmd's AI chat widget is turned off there. `llms.txt` and an OKF bundle are generated alongside the site (see DVSA RFC-005).
