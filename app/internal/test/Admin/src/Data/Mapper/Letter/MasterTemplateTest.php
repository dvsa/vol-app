<?php

declare(strict_types=1);

namespace AdminTest\Data\Mapper\Letter;

use Admin\Data\Mapper\Letter\MasterTemplate;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see MasterTemplate
 */
final class MasterTemplateTest extends MockeryTestCase
{
    public function testMapFromFormMapsEmptyLocaleToNull(): void
    {
        // The locale select's empty option submits '' — that must reach the command
        // as null so the Update handler leaves a NULL locale untouched instead of
        // coercing pre-VOL-7305 rows to a concrete locale on unrelated saves.
        $commandData = MasterTemplate::mapFromForm([
            'masterTemplate' => [
                'id' => 1,
                'name' => 'Default Letter Template',
                'locale' => '',
            ],
        ]);

        $this->assertNull($commandData['locale']);
    }

    public function testMapFromFormPassesConcreteLocaleThrough(): void
    {
        $commandData = MasterTemplate::mapFromForm([
            'masterTemplate' => [
                'id' => 1,
                'name' => 'Default Letter Template',
                'locale' => 'en_NI',
            ],
        ]);

        $this->assertSame('en_NI', $commandData['locale']);
    }

    public function testMapFromFormDecodesSlotJsonStrings(): void
    {
        $slotJson = '{"time":1,"version":"2.31.0","blocks":[{"id":"a","type":"paragraph","data":{"text":"Hi"}}]}';

        $commandData = MasterTemplate::mapFromForm([
            'masterTemplate' => [
                'id' => 1,
                'name' => 'Default Letter Template',
                'headerLeftContent' => $slotJson,
                'signoffContent' => '',
            ],
        ]);

        $this->assertSame('Hi', $commandData['headerLeftContent']['blocks'][0]['data']['text']);
        $this->assertNull($commandData['signoffContent']);
    }
}
