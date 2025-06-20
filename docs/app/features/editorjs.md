---
sidebar_position: 10
title: EditorJS Rich Text Editor
---

# EditorJS Rich Text Editor

EditorJS is a modern block-based rich text editor that replaces TinyMCE for submission comments in the VOL application. It provides better British English spell checking support and a cleaner editing experience.

## Overview

The EditorJS implementation spans both the API and Internal applications:

- **Internal App**: Provides the form element, view helper, and HTML→JSON conversion
- **API**: Handles JSON→HTML conversion for database storage
- **Frontend**: JavaScript component for editor initialization and modal support

## Data Flow

### Key Point: JSON is the Exchange Format

**Important**: The API and Internal apps communicate using **JSON only**. HTML conversion happens at the boundaries:

- **HTML → JSON**: Only in Internal app when loading legacy HTML content
- **JSON → HTML**: Only in API when saving to database

### Editing Existing Content

```
Database (HTML) → API serves HTML → Internal converts HTML to JSON →
EditorJS (JSON) → User edits → JSON sent to API →
API converts JSON to HTML → Database (HTML)
```

### Creating New Content

```
Empty EditorJS → User creates content → EditorJS JSON →
JSON sent to API → API converts JSON to HTML → Database (HTML)
```

## Implementation Details

### Form Element (Internal)

The EditorJS form element accepts both JSON and HTML input but always works with JSON internally:

```php
// In your form configuration
'comment' => [
    'type' => \Olcs\Form\Element\EditorJs::class,
    'name' => 'comment',
    'options' => [
        'label' => 'submission-decision-comment',
    ],
    'attributes' => [
        'class' => 'extra-long',
        'required' => false,
    ],
],
```

The form element uses a factory pattern for dependency injection:

```php
// module.config.php
'form_elements' => [
    'factories' => [
        \Olcs\Form\Element\EditorJs::class => \Olcs\Form\Element\EditorJsFactory::class,
    ],
    'aliases' => [
        'EditorJs' => \Olcs\Form\Element\EditorJs::class
    ]
],
```

### HTML to JSON Conversion (Internal)

The `HtmlConverter` service converts legacy HTML content to EditorJS JSON format:

```php
// Usage in form element
$converter = new \Olcs\Service\EditorJs\HtmlConverter();
$json = $converter->convertHtmlToJson($htmlContent);
```

Supported HTML elements:

- `<p>` → paragraph blocks
- `<ul>`, `<ol>` → list blocks
- `<h1>` to `<h6>` → header blocks
- Inline formatting: `<b>`, `<i>`, `<u>`, `<s>`, `<mark>`, `<code>`, `<a>`

### JSON to HTML Conversion (API)

The API uses the Setono EditorJS library for converting JSON back to HTML:

```php
// In command handlers
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\EditorJsConversionTrait;

final class UpdateSubmissionSectionComment extends AbstractCommandHandler
{
    use EditorJsConversionTrait;

    public function handleCommand(CommandInterface $command)
    {
        // Automatically converts JSON to HTML
        $htmlContent = $this->convertCommentForStorage($command->getComment());
        $entity->setComment($htmlContent);
    }
}
```

#### Future Use Cases

The `ConverterService` is initially implemented to maintain compatibility with the existing submission comments system that expects HTML. However, it's a reusable service that can be leveraged for other purposes:

```php
// Direct usage for rendering templates
$converterService = $container->get(\Dvsa\Olcs\Api\Service\EditorJs\ConverterService::class);
$html = $converterService->convertJsonToHtml($editorJsJson);

// Use cases:
// - Rendering HTML emails from EditorJS content
// - Generating PDF documents with formatted text
// - Displaying read-only content in reports
// - Exporting submission data with formatting
```

This makes EditorJS a versatile solution for any rich text needs across the VOL platform.

### JavaScript Component

The EditorJS JavaScript component handles initialization and modal compatibility:

```javascript
// Automatic initialization via OLCS pattern
OLCS.editorjs();

// Handles:
// - Multiple editor instances
// - Modal compatibility
// - Cleanup on modal close
// - Fallback for browser compatibility
```

## EditorJS Features

### Supported Block Types

- **Paragraph**: Standard text blocks with inline formatting
- **Header**: Six levels of headers (H1-H6)
- **List**: Ordered and unordered lists

### Inline Formatting

- **Bold** (`Ctrl/Cmd + B`)
- **Italic** (`Ctrl/Cmd + I`)
- **Link** (`Ctrl/Cmd + K`)

### Benefits Over TinyMCE

- ✅ Native browser spell checking (supports British English properly)
- ✅ Cleaner, modern interface
- ✅ Better performance in modals
- ✅ Structured JSON data format
- ✅ No external spell checker dependencies

## Adding EditorJS to New Forms

### 1. Add to Form Configuration

```php
// In your form class
'myField' => [
    'type' => \Olcs\Form\Element\EditorJs::class,
    'name' => 'myField',
    'options' => [
        'label' => 'field-label',
    ],
    'attributes' => [
        'class' => 'extra-long',
        'required' => false,
    ],
],
```

### 2. Update Command Handler

For new tables storing JSON directly:

```php
// No conversion needed - store JSON as-is
$entity->setContent($command->getContent());
```

For existing tables storing HTML:

```php
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\EditorJsConversionTrait;

class MyCommandHandler extends AbstractCommandHandler
{
    use EditorJsConversionTrait;

    public function handleCommand(CommandInterface $command)
    {
        // Converts JSON to HTML if needed
        $content = $this->convertCommentForStorage($command->getContent());
        $entity->setContent($content);
    }
}
```

### 3. Ensure JavaScript Loads

The EditorJS component is automatically initialized in internal.js. No additional setup required.

## Troubleshooting

### Editor Not Appearing

- Check browser console for JavaScript errors
- Verify EditorJS libraries are loaded
- Ensure form element has unique ID

### Content Not Saving

- Verify command handler has conversion trait
- Check JSON structure in browser network tab
- Ensure form field name matches DTO property

### Formatting Lost on Edit

- Verify HtmlConverter preserves required tags
- Check inline formatting tags are supported
- Test with various HTML content structures

## Technical Reference

### File Locations

**Internal App:**

- Form Element: `/app/internal/module/Olcs/src/Form/Element/EditorJs.php`
- Factory: `/app/internal/module/Olcs/src/Form/Element/EditorJsFactory.php`
- View Helper: `/app/internal/module/Olcs/src/Form/View/Helper/EditorJs.php`
- HTML Converter: `/app/internal/module/Olcs/src/Service/EditorJs/HtmlConverter.php`
- Filter: `/app/internal/module/Olcs/src/Filter/EditorJsFilter.php`

**API:**

- Converter Service: `/app/api/module/Api/src/Service/EditorJs/ConverterService.php`
- Conversion Trait: `/app/api/module/Api/src/Domain/CommandHandler/Traits/EditorJsConversionTrait.php`
- Aware Interface: `/app/api/module/Api/src/Domain/EditorJsConverterAwareInterface.php`

**Frontend Assets:**

- JavaScript: `/app/cdn/assets/_js/components/editorjs.js`
- Initialization: `/app/cdn/assets/_js/init/internal.js`

### Dependencies

**Composer Packages:**

```json
{
    "setono/editorjs-php": "^1.3"
}
```

**NPM Packages:**

```json
{
    "@editorjs/editorjs": "^2.28.2",
    "@editorjs/header": "^2.8.1",
    "@editorjs/list": "^1.9.0",
    "@editorjs/paragraph": "^2.11.3"
}
```
