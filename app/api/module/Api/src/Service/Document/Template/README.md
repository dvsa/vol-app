# Repo-tracked document templates

Templates in this directory are served **from disk at runtime**:
`DocumentGenerator::readLocalTemplate()` (the class beside this directory)
checks here first and only falls back to the document store for templates
that are not present — the same convention as the bookmark snippets in
`Bookmark/Snippet/`. Anything here ships atomically with the deploy — no
doc-store upload step, no risk of the template being missing or edited
out-of-band. The layout mirrors the doc store's `templates/` root (a
template resolved as `/templates/GB/Foo.rtf` would live at `GB/Foo.rtf`
here).

## Disc printing (Gotenberg renderer)

These are the counterpart of the `*_DISC_PINNED_LAYOUT` SystemParameter
toggles — when a toggle is on, `Discs\PrintDiscs` requests these template
names instead of the legacy `GVDiscTemplate`/`PSVDiscTemplate` (which stay
untouched in the doc store as the bail-out path).

| file                           | derived from                    | changes vs legacy                                                                                                                                                                                                                              |
| ------------------------------ | ------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `GVDiscTemplateGotenberg.rtf`  | doc-store `GVDiscTemplate.rtf`  | `\margt`/`\margl` literals replaced by the `Gv_Disc_Margins` bookmark (SystemParameter-driven page position)                                                                                                                                   |
| `PSVDiscTemplateGotenberg.rtf` | doc-store `PSVDiscTemplate.rtf` | adds the explicit page setup the legacy shell never had (A4, margins via the `Psv_Disc_Margins` bookmark, `\margb500`, and the legacy Word compatibility flags incl. `\nolnhtadjtbl` that LibreOffice needs to honour exact table row heights) |

Rollout order for the disc alignment fixes: deploy the release carrying
these templates, apply the SystemParameter rows (olcs-etl
`patches/8.2.0/disc-alignment-calibrated-params.sql`, toggles seeded '0'),
then flip a `*_DISC_PINNED_LAYOUT` toggle to `1` inside a change window
after a proof print on real stationery. Flipping the toggle on a release
that predates these templates fails the print run loudly (template not
found) rather than printing a mismatched layout onto controlled stationery.
