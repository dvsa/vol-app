<?php

declare(strict_types=1);

namespace AdminTest\Data\Mapper;

use Admin\Data\Mapper\LongText;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LongText::class)]
final class LongTextTest extends TestCase
{
    public function testItEncodesApiContentForTheEditor(): void
    {
        $result = LongText::mapFromResult([
            'id' => 7,
            'referenceKey' => 'application-declaration-gv79-gb',
            'pageName' => 'New application declaration',
            'description' => 'Shown above the signature',
            'content' => ['blocks' => [['id' => 'a', 'type' => 'paragraph', 'data' => ['text' => 'I confirm']]]],
        ]);

        self::assertSame(7, $result['longTextDetails']['id']);
        self::assertSame('application-declaration-gv79-gb', $result['longTextDetails']['referenceKey']);
        self::assertJsonStringEqualsJsonString(
            '{"blocks":[{"id":"a","type":"paragraph","data":{"text":"I confirm"}}]}',
            $result['longTextDetails']['content'],
        );
    }

    public function testItLeavesContentAloneWhenTheApiAlreadySentAString(): void
    {
        $result = LongText::mapFromResult(['id' => 7, 'content' => '{"blocks":[]}']);

        self::assertSame('{"blocks":[]}', $result['longTextDetails']['content']);
    }

    public function testItFlattensFormDataBackForTheCommand(): void
    {
        $data = LongText::mapFromForm([
            'longTextDetails' => [
                'referenceKey' => 'application-declaration-gv79-gb',
                'pageName' => 'New application declaration',
                'content' => '{"blocks":[]}',
            ],
        ]);

        self::assertSame('application-declaration-gv79-gb', $data['referenceKey']);
        self::assertSame('{"blocks":[]}', $data['content']);
    }
}
