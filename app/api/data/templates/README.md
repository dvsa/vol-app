# Doc-store templates tracked in the repo

Disc-printing base templates for the Gotenberg/LibreOffice renderer. These
are **not read from the repo at runtime** — the app fetches them from the
document store, so rolling them out to an environment means uploading each
file to that environment's doc-store bucket under `templates/<name>.rtf`
(e.g. `s3://olcs-<env>-base-sabredav/migration/olcs/templates/`).

They are tracked here for provenance and review: they are the counterpart
of the `*_DISC_PINNED_LAYOUT` SystemParameter toggles — when a toggle is on,
`Dvsa\Olcs\Api\Domain\CommandHandler\Discs\PrintDiscs` requests these
template names instead of the legacy `GVDiscTemplate`/`PSVDiscTemplate`
(which stay untouched in the doc store as the bail-out path).

| file                           | derived from                    | changes vs legacy                                                                                                                                                                                                                              |
| ------------------------------ | ------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `GVDiscTemplateGotenberg.rtf`  | doc-store `GVDiscTemplate.rtf`  | `\margt`/`\margl` literals replaced by the `Gv_Disc_Margins` bookmark (SystemParameter-driven page position)                                                                                                                                   |
| `PSVDiscTemplateGotenberg.rtf` | doc-store `PSVDiscTemplate.rtf` | adds the explicit page setup the legacy shell never had (A4, margins via the `Psv_Disc_Margins` bookmark, `\margb500`, and the legacy Word compatibility flags incl. `\nolnhtadjtbl` that LibreOffice needs to honour exact table row heights) |

Upload order matters: these templates (and the SystemParameter rows from
olcs-etl `patches/8.2.0/disc-alignment-calibrated-params.sql`) must be in
place **before** a `*_DISC_PINNED_LAYOUT` toggle is set to `1`, otherwise
disc print runs fail with template-not-found (deliberately loud, rather
than printing a mismatched layout onto controlled stationery).
