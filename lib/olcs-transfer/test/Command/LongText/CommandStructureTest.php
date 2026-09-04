<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\LongText;

use Dvsa\Olcs\Transfer\Command\LongText\Create;
use Dvsa\Olcs\Transfer\Command\LongText\Update;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Create::class)]
#[CoversClass(Update::class)]
final class CommandStructureTest extends TestCase
{
    public function testCreateCarriesTheAuthoredContent(): void
    {
        $command = Create::create([
            'referenceKey' => 'application-declaration-gv79-gb',
            'pageName' => 'New application declaration',
            'description' => 'Shown above the signature',
            'content' => '{"blocks":[]}',
        ]);

        self::assertSame('application-declaration-gv79-gb', $command->getReferenceKey());
        self::assertSame('New application declaration', $command->getPageName());
        self::assertSame('Shown above the signature', $command->getDescription());
        self::assertSame('{"blocks":[]}', $command->getContent());
    }

    public function testUpdateHasNoReferenceKeyField(): void
    {
        $command = Update::create([
            'id' => 7,
            'pageName' => 'New name',
            'description' => null,
            'content' => '{"blocks":[]}',
        ]);

        self::assertSame(7, $command->getId());
        self::assertArrayNotHasKey('referenceKey', $command->getArrayCopy());
    }
}
