<?php

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
     * @dataProvider provider
     *
     * @param $canAccess
     * @param $expected
     */
    public function testIsValid($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(76);

        $this->setIsValid('canAccessDocument', [76], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    /**
     * @dataProvider provider
     *
     * @param $canAccess
     * @param $expected
     */
    public function testIsValidWithIdentifier($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = \Dvsa\Olcs\Transfer\Query\Document\Download::create(['identifier' => 76]);

        $this->setIsValid('canAccessDocument', [76], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function provider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
