<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Opposition;

use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Api\Entity\Opposition\Opposition as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Opposition\Opposition::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Opposition\AbstractOpposition::class)]
final class OppositionEntityTest extends EntityTester
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

        $this->assertSame($mockCase, $sut->getCase());
        $this->assertSame($mockOpposer, $sut->getOpposer());
        $this->assertSame($oppositionType, $sut->getOppositionType());

        $this->assertEquals($isValid, $sut->getIsValid());
        $this->assertEquals($isWithdraw, $sut->getIsWithdrawn());
        $this->assertEquals($isCopied, $sut->getIsCopied());
        $this->assertEquals($isInTime, $sut->getIsInTime());
        $this->assertEquals($isWillingToAttendPi, $sut->getIsWillingToAttendPi());
    }
}
