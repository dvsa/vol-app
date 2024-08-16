<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Bus\Ebsr;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Bus\Ebsr\CanAccessTxcInboxRecordWithId;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Handlers\Bus\Ebsr\CanAccessTxcInboxRecordWithId
 */
class CanAccessTxcInboxRecordWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessTxcInboxRecordWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessTxcInboxRecordWithId();

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
        $id = 111;
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn($id);

        $this->setIsValid('canAccessTxcInbox', [$id], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    /**
     * @return array
     */
    public function provider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
