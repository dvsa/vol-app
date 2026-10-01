<?php

declare(strict_types=1);

namespace AdminTest\Data\Mapper\Letter;

use Admin\Data\Mapper\Letter\LetterSectionVariantEdit;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @see LetterSectionVariantEdit
 */
final class LetterSectionVariantEditTest extends MockeryTestCase
{
    /**
     * The select helper matches on strval(), and strval(false) is '' - the "Any" option -
     * so the flags must reach the form as '0'/'1' or New Application and GB show as Any
     */
    #[DataProvider('dpFlagValues')]
    public function testFlagsMapToSelectOptionValues(?bool $apiValue, ?string $expected): void
    {
        $formData = LetterSectionVariantEdit::mapFromResult([
            'id' => 34,
            'isVariation' => $apiValue,
            'isNi' => $apiValue,
        ]);

        $this->assertSame($expected, $formData['letterSectionVariant']['isVariation']);
        $this->assertSame($expected, $formData['letterSectionVariant']['isNi']);
    }

    public static function dpFlagValues(): array
    {
        return [
            'false is New Application / GB' => [false, '0'],
            'true is Variation / NI' => [true, '1'],
            'null is Any' => [null, null],
        ];
    }

    #[DataProvider('dpApiValues')]
    public function testFlagsRoundTrip(?bool $apiValue): void
    {
        $formData = LetterSectionVariantEdit::mapFromResult([
            'id' => 34,
            'isVariation' => $apiValue,
            'isNi' => $apiValue,
        ]);
        $commandData = LetterSectionVariantEdit::mapFromForm($formData);

        $this->assertSame($apiValue, $commandData['isVariation']);
        $this->assertSame($apiValue, $commandData['isNi']);
    }

    public static function dpApiValues(): array
    {
        return [
            'false' => [false],
            'true' => [true],
            'null' => [null],
        ];
    }
}
