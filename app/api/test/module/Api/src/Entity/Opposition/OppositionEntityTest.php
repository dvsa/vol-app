<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Opposition;

use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Api\Entity\Opposition\Opposition as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Entity\Opposition\Opposition
 * @covers Dvsa\Olcs\Api\Entity\Opposition\AbstractOpposition
 */
class OppositionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstructor(): void
    {
        /** @var Entities\Cases\Cases $mockCase */
        $mockCase = m::mock(Entities\Cases\Cases::class);
        /** @var Entities\Opposition\Opposer $mockOpposer */
        $mockOpposer = m::mock(Entities\Opposition\Opposer::class);
        $oppositionType = new Entities\System\RefData(Entities\Opposition\Opposition::OPPOSITION_TYPE_ENV);

        $isValid = 'unit_isValid';
        $isCopied = 'unit_isCopied';
        $isInTime = 'unit_isInTime';
        $isWillingToAttendPi = 'unit_IsWillingToAttendPi';
        $isWithdraw = 'unit_IsWithdraw';

        $sut = new Entity(
            $mockCase,
            $mockOpposer,
            $oppositionType,
            $isValid,
            $isCopied,
            $isInTime,
            $isWillingToAttendPi,
            $isWithdraw
        );

        static::assertSame($mockCase, $sut->getCase());
        static::assertSame($mockOpposer, $sut->getOpposer());
        static::assertSame($oppositionType, $sut->getOppositionType());

        static::assertEquals($isValid, $sut->getIsValid());
        static::assertEquals($isWithdraw, $sut->getIsWithdrawn());
        static::assertEquals($isCopied, $sut->getIsCopied());
        static::assertEquals($isInTime, $sut->getIsInTime());
        static::assertEquals($isWillingToAttendPi, $sut->getIsWillingToAttendPi());
    }
}
