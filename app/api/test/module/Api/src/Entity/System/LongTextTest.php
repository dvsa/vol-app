<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\System;

use Dvsa\Olcs\Api\Entity\System\LongText;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LongTextTest extends TestCase
{
    public function testItIsCreatedWithAReferenceKeyAndContent(): void
    {
        $longText = LongText::create(
            'application-declaration-gv79-gb',
            'cy_GB',
            'New application declaration (GB, goods)',
            'Shown above the signature on the review and declarations page',
            ['blocks' => []],
        );

        self::assertSame('application-declaration-gv79-gb', $longText->getReferenceKey());
        self::assertSame('cy_GB', $longText->getLocale());
        self::assertSame('New application declaration (GB, goods)', $longText->getPageName());
        self::assertSame(['blocks' => []], $longText->getContent());
    }

    #[DataProvider('invalidReferenceKeyProvider')]
    public function testItRejectsAReferenceKeyThatCannotBeUsedFromCode(string $key): void
    {
        $this->expectException(\InvalidArgumentException::class);

        LongText::create($key, 'en_GB', 'Page', null, ['blocks' => []]);
    }

    public static function invalidReferenceKeyProvider(): array
    {
        return [
            'empty' => [''],
            'spaces' => ['application declaration'],
            'uppercase' => ['Application-Declaration'],
            'underscores' => ['application_declaration'],
            'trailing hyphen' => ['application-declaration-'],
        ];
    }

    public function testItRejectsALocaleVolDoesNotServe(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        LongText::create('application-declaration', 'fr_FR', 'Page', null, ['blocks' => []]);
    }

    public function testItRecordsWhenItWasCreatedAndLastUpdated(): void
    {
        $longText = LongText::create('application-declaration', 'en_GB', 'Page', null, ['blocks' => []]);

        self::assertNull($longText->getLastModifiedOn());

        $longText->setCreatedOnBeforePersist();
        $longText->setLastModifiedOnBeforeUpdate();

        self::assertInstanceOf(\DateTime::class, $longText->getCreatedOn(true));
        self::assertInstanceOf(\DateTime::class, $longText->getLastModifiedOn(true));
    }
}
