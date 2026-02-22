<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Document;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Document\CanAccessDocumentWithId;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Handlers\Document\CanAccessDocumentWithId
 */
class CanAccessDocumentWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessDocumentWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessDocumentWithId();

        parent::setUp();
    }

    /**
     *
     * @param $canAccess
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(76);

        $this->setIsValid('canAccessDocument', [76], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    /**
     *
     * @param $canAccess
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValidWithIdentifier(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = \Dvsa\Olcs\Transfer\Query\Document\Download::create(['identifier' => 76]);

        $this->setIsValid('canAccessDocument', [76], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function provider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
