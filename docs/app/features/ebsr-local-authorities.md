---
sidebar_position: 60
title: EBSR Local Authorities
---

# EBSR Local Authorities

An EBSR file names the local transport authorities a bus registration is circulated to, using
TransXChange's list of authority names, and its stops carry NaPTAN ATCO codes whose first three
characters say which area each stop is in. VOL turns both into `local_authority` records: the
circulated ones are attached to the registration (and get the emails and TXC inbox entries), and
the stop ones are checked against them.

DfT's April 2026 update to the authority list (PTIC-106, TransXChange 2.4.1 / 2.5.2) replaced many
councils with combined authorities, renamed some, and split Northamptonshire. Operators keep
sending old names and old ATCO codes for years, so VOL accepts both.

## How old and new names meet

Old `local_authority` rows are kept as they are: their descriptions are public, and existing
registrations stay linked to them. Each new authority has its own row, and
`local_authority_successor` links every old row to the row(s) that replaced it (a split authority
has two).

```text
Darlington (txc Darlington, ATCO 076) ──┐
Stockton   (txc StocktonOnTees, 077) ───┼──> Tees Valley Combined Authority (txc TeesValleyCA)
TeesValleyCA (new name, no ATCO code) ──┘
```

`LocalAuthority::getCurrentAuthorities()` follows the links to the authorities that stand for a row
now. During EBSR processing (`AbstractProcessPack`, `LocalAuthorityResolver`):

- **Circulated names** are trimmed, looked up as before, then replaced by their current
  authorities, each once. An old split name (Northamptonshire) narrows to the side(s) the route's
  stops are in, or keeps every side if the stops don't say.
- **Stop codes** are looked up as before. `LocalAuthorityMissing` then needs, for each record the
  stops matched, at least one of its current authorities among the circulated ones. A record with
  no successor must be circulated itself, which is the rule before the links existed.

So an old name with an old code, a new name with an old code, or a mixture of both all reach the
same authority and pass.

- **Traffic areas** come from the councils involved, not from the authority that now stands for
  them: a combined authority has one traffic area but can cover councils in several. A council
  the file names gives its own; a current authority the file names gives those of the councils its
  stops are in, or its own when none of the stops is.

## Elsewhere

- **Local authority users** see TXC inbox items, documents and registrations for the authority
  that stands for theirs now and every authority that one replaced
  (`LocalAuthority::getAuthorityIdsVisibleToUsers()`). A user can stay attached to a replaced
  council's row: they work for the authority that replaced it. A local authority admin manages the
  users of their own authority and of those it replaced (`getIdsIncludingPredecessors()`), never
  those of the authority that replaced theirs or of its other councils. The inbox lists an older registration once even when it was
  circulated to several of those councils.
- **Emails** about a registration (`SendBSRNotificationToLTAs`, the EBSR emails) go to its
  authorities and to the authorities that replaced them, each address once, and copy the users of
  those authorities and of the councils they replaced (`LocalAuthority::withCurrentAuthorities()`,
  `getUsersIncludingPredecessors()`).
- **The internal TA & authorities form** offers only current authorities, plus any replaced one the
  registration already has. `LocalAuthorityList` flags replaced rows with `isSuperseded`, and lists
  label them "(replaced)": a replaced council often keeps the public name of the authority that
  replaced it.

## Changing the mapping

The data lives in olcs-etl (`patches/9.6.0/VOL-7153-local-authority-successors.sql`): new rows,
TransXChange names, ATCO codes and links. A later change is another patch in the same shape. Add a
row for a new authority, link the old row(s) to it, and move an ATCO code to the new row only when
NaPTAN has given the code to it alone.

Deploy the vol-app code before the data. The code without links behaves as it did before them, but
links and moved ATCO codes on older code reject files that name the old authority.

Then regenerate the integration baseline and review the diff. It lists every TransXChange name in
the bundled schemas and every NaPTAN ATCO code (`app/api/test/integration/naptan-atco-area-codes.txt`,
from NaPTAN's [ATCO codes in use](https://beta-naptan.dft.gov.uk/article/atco-codes-in-use)) with
the authority it resolves to:

```bash
# app/api, with the local database refreshed
REGENERATE_LOCAL_AUTHORITY_BASELINE=1 vendor/bin/phpunit \
  -c phpunit-integration.xml --filter LocalAuthorityResolutionTest
```

## Tests

| Layer                                                 | Test                                                                    |
| ----------------------------------------------------- | ----------------------------------------------------------------------- |
| Following links                                       | `LocalAuthorityEntityTest`                                              |
| Name and stop resolution                              | `LocalAuthorityResolverTest`, `LocalAuthorityMissingTest`               |
| Scenarios (old/new names and codes, splits)           | `LocalAuthorityScenarioTest`                                            |
| Whole file, through the real schema check and mapping | `TransXChangeExamplesTest::testCirculatedAuthoritiesResolveFromTheFile` |
| Real data: every name and code                        | `LocalAuthorityResolutionTest` (integration suite)                      |
