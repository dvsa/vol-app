---
sidebar_position: 40
title: Long Text
---

# Long Text

Long Text allows System Admins to maintain longer sections of page wording from **Admin > Content Management > Long Text**. The content is edited with EditorJS, stored as JSON and rendered as GOV.UK HTML when the page is loaded.

Each record has a UID, page name, description, language and content. Application code uses the UID rather than the database ID, as database IDs can differ between environments.

## Page content

Long Text is used by the following self-service journeys:

- new application review and declarations
- variation review and declarations
- continuation declaration, including the printable version
- transport manager declaration

The existing services still decide which version of the wording applies, for example goods or PSV, licence type and GB or Northern Ireland. Long Text only changes where that wording is loaded from. Form fields, buttons and other page controls remain in the application code.

`LongTextTranslator` maps a markup key to its UID by removing `markup-`, replacing underscores with hyphens and converting it to lower case. For example:

```text
markup-application_declaration_goods_gb -> application-declaration-goods-gb
```

Content is loaded for the current language. If there is no matching record, the existing translation or partial is used. Unexpected loading or rendering errors are logged and allowed to fail normally rather than being hidden by the fallback.

## Rendering

`LongTextConverterService` converts EditorJS blocks into HTML with GOV.UK classes. It is separate from the converter used for letters, which produces plain HTML for document output.

## Migrating existing wording

The migration utility converts the declaration partials used by these four journeys into EditorJS JSON and produces the SQL used to seed the `long_text` table:

```bash
app/api/data/db/convert-long-text-partials.php > /tmp/VOL-7358-long-text-seed.sql
```

The utility stops and reports the affected partial if it finds content it cannot convert safely. The deployed seed is held in `olcs-etl/patches/9.2.0/VOL-7358-long-text-seed.sql` and registered in the 9.2.0 data changeset.

## Main files

- `app/api/module/Api/src/Service/LongText/LongTextTranslator.php`
- `app/api/module/Api/src/Service/EditorJs/LongTextConverterService.php`
- `app/api/module/Api/src/Service/EditorJs/HtmlToEditorJsConverter.php`
- `app/internal/module/Admin/src/Controller/LongTextController.php`
- `app/internal/module/Admin/src/Table/Tables/admin-long-text.table.php`
