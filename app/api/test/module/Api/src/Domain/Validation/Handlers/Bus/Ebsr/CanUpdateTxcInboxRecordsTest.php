<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Bus\Ebsr;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Bus\Ebsr\CanUpdateTxcInboxRecords;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Validation\Handlers\Bus\Ebsr\CanUpdateTxcInboxRecords::class)]
final class CanUpdateTxcInboxRecordsTest extends AbstractHandlerTestCase
{
    /**
     * @var CanUpdateTxcInboxRecords
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanUpdateTxcInboxRecords();

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
        $id = 111;
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getIds')->andReturn([$id]);

        $this->setIsValid('canUpdateTxcInbox', [[$id]], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield [true, true];
        yield [false, false];
    }
}
