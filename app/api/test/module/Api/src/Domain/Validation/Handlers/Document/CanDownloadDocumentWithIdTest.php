<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Document;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Document\CanDownloadDocumentWithId;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Validation\Handlers\Document\CanDownloadDocumentWithId::class)]
final class CanDownloadDocumentWithIdTest extends AbstractHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CanDownloadDocumentWithId();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(76);

        $this->setIsValid('canDownloadDocument', [76], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValidWithIdentifier(mixed $canAccess, mixed $expected): void
    {
        $dto = \Dvsa\Olcs\Transfer\Query\Document\Download::create(['identifier' => 76]);

        $this->setIsValid('canDownloadDocument', [76], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function provider(): \Iterator
    {
        yield [true, true];
        yield [false, false];
    }
}
