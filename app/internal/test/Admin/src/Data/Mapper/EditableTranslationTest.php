<?php

declare(strict_types=1);

namespace AdminTest\Data\Mapper;

use Admin\Data\Mapper\EditableTranslation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EditableTranslation::class)]
final class EditableTranslationTest extends TestCase
{
    public function testPlainTranslationsRemainBase64EncodedStrings(): void
    {
        self::assertSame([
            'translationKey' => 'example',
            'translationsArray' => ['en_GB' => base64_encode('Hello')],
        ], EditableTranslation::mapFromForm([
            'fields' => [
                'translationKey' => 'example',
                'translationsArray' => ['en_GB' => 'Hello', 'cy_GB' => ''],
            ],
        ]));
    }

    public function testRichTranslationsUseTheSameCommandMapWithoutEmptyLanguages(): void
    {
        $json = '{"blocks":[{"type":"paragraph","data":{"text":"Hello"}}]}';

        self::assertSame([
            'translationKey' => 'markup-example',
            'format' => 'editorjs',
            'translationsArray' => ['en_GB' => base64_encode($json)],
        ], EditableTranslation::mapFromForm([
            'fields' => [
                'translationKey' => 'markup-example',
                'format' => 'editorjs',
                'translationsArray' => ['en_GB' => $json, 'cy_GB' => ''],
            ],
        ]));
    }

    public function testRichTranslationsCannotSubmitHtmlAsEditorJson(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        EditableTranslation::mapFromForm([
            'fields' => [
                'format' => 'editorjs',
                'translationsArray' => ['en_GB' => '<p>Old HTML</p>'],
            ],
        ]);
    }

    public function testEmptyRichFormLeavesLanguagesEmptyForApiValidation(): void
    {
        self::assertSame([
            'translationKey' => 'example',
            'format' => 'editorjs',
            'translationsArray' => [],
        ], EditableTranslation::mapFromForm([
            'fields' => ['translationKey' => 'example', 'format' => 'editorjs'],
        ]));
    }
}
