<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\CommunityLic;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspension;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspensionReason;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicWithdrawal;
use Mockery as m;
use Doctrine\Common\Collections\Criteria;

/**
 * CommunityLic Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class CommunityLicEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = CommunityLicEntity::class;

    public function testUpdateCommunityLic(): void
    {
        $data = [
            'status' => 'status',
            'specifiedDate' => '2015-01-01',
            'serialNoPrefix' => 'A',
            'licence' => 1,
            'issueNo' => 0
        ];

        $sut = m::mock(CommunityLicEntity::class)->makePartial();
        $sut->updateCommunityLic($data);

        $this->assertEquals('status', $sut->getStatus());
        $this->assertEquals('2015-01-01', $sut->getSpecifiedDate());
        $this->assertEquals('A', $sut->getSerialNoPrefix());
        $this->assertEquals(1, $sut->getLicence());
        $this->assertEquals(0, $sut->getIssueNo());
    }

    public function testChangeStatusAndExpiryDate(): void
    {
        $sut = m::mock(CommunityLicEntity::class)->makePartial();
        $sut->changeStatusAndExpiryDate('status', '2015-01-01');
        $this->assertEquals('status', $sut->getStatus());
        $this->assertEquals('2015-01-01', $sut->getExpiredDate());
    }

    public function testGetCalculatedBundleValues(): void
    {
        $sut = m::mock(CommunityLicEntity::class)->makePartial()
            ->shouldReceive('getStatus')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(CommunityLicEntity::STATUS_PENDING)
                ->times(3)
                ->getMock()
            )
            ->times(3)
            ->getMock();

        $expected = [
            'futureSuspension' => null,
            'currentSuspension' => null,
            'currentWithdrawal' => null,
        ];

        $this->assertEquals($expected, $sut->getCalculatedBundleValues());
    }

    public function testGetFutureSuspension(): void
    {
        $suspension = new CommunityLicSuspension();

        $type1 = m::mock(RefData::class)->makePartial()
            ->setId('foo');

        $type2 = m::mock(RefData::class)->makePartial()
            ->setId('bar');

        $reason1 = new CommunityLicSuspensionReason($suspension, $type1);
        $reason2 = new CommunityLicSuspensionReason($suspension, $type2);

        $reasons = new ArrayCollection();
        $reasons->add($reason1);
        $reasons->add($reason2);

        $suspension->setStartDate(new \DateTime('3016-01-01'));
        $suspension->setEndDate(new \DateTime('3017-01-01'));
        $suspension->setId(1);
        $suspension->setVersion(2);
        $suspension->setCommunityLicSuspensionReasons($reasons);

        $suspensions = new ArrayCollection();
        $suspensions->add($suspension);

        $sut = m::mock(CommunityLicEntity::class)->makePartial()
            ->shouldReceive('getStatus')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(CommunityLicEntity::STATUS_ACTIVE)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getCommunityLicSuspensions')
            ->andReturn(
                m::mock()
                    ->shouldReceive('matching')
                    ->with(m::type(Criteria::class))
                    ->andReturn($suspensions)
                    ->getMock()
            )
            ->once()
            ->getMock();

        $result = $sut->getFutureSuspension();

        $expected = [
            'startDate' => new \DateTime('3016-01-01'),
            'endDate' => new \DateTime('3017-01-01'),
            'reasons' => ['foo', 'bar'],
            'id' => 1,
            'version' => 2
        ];

        $this->assertEquals($result, $expected);
    }

    public function testGetCurrentSuspension(): void
    {
        $suspension = new CommunityLicSuspension();

        $type1 = m::mock(RefData::class)->makePartial()
            ->setId('foo');

        $type2 = m::mock(RefData::class)->makePartial()
            ->setId('bar');

        $reason1 = new CommunityLicSuspensionReason($suspension, $type1);
        $reason2 = new CommunityLicSuspensionReason($suspension, $type2);

        $reasons = new ArrayCollection();
        $reasons->add($reason1);
        $reasons->add($reason2);

        $suspension->setStartDate(new \DateTime('2000-01-01'));
        $suspension->setEndDate(new \DateTime('3016-01-01'));
        $suspension->setId(1);
        $suspension->setVersion(2);
        $suspension->setCommunityLicSuspensionReasons($reasons);

        $suspensions = new ArrayCollection();
        $suspensions->add($suspension);

        $sut = m::mock(CommunityLicEntity::class)->makePartial()
            ->shouldReceive('getStatus')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(CommunityLicEntity::STATUS_SUSPENDED)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getCommunityLicSuspensions')
            ->andReturn(
                m::mock()
                    ->shouldReceive('matching')
                    ->with(m::type(Criteria::class))
                    ->andReturn($suspensions)
                    ->getMock()
            )
            ->once()
            ->getMock();

        $result = $sut->getCurrentSuspension();

        $expected = [
            'startDate' => new \DateTime('2000-01-01'),
            'endDate' => new \DateTime('3016-01-01'),
            'reasons' => ['foo', 'bar'],
            'id' => 1,
            'version' => 2
        ];

        $this->assertEquals($result, $expected);
    }

    public function testGetCurrentWithdrawal(): void
    {
        $withdrawal = new CommunityLicWithdrawal();

        $withdrawal->setStartDate(new \DateTime('2000-01-01'));
        $withdrawal->setId(1);

        $withdrawals = new ArrayCollection();
        $withdrawals->add($withdrawal);

        $sut = m::mock(CommunityLicEntity::class)->makePartial()
            ->shouldReceive('getStatus')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(CommunityLicEntity::STATUS_WITHDRAWN)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getCommunityLicWithdrawals')
            ->andReturn(
                m::mock()
                ->shouldReceive('matching')
                ->with(m::type(Criteria::class))
                ->andReturn($withdrawals)
                ->getMock()
            )
            ->once()
            ->getMock();

        $result = $sut->getCurrentWithdrawal();

        $expected = [
            'startDate' => new \DateTime('2000-01-01'),
            'id' => 1,
        ];

        $this->assertEquals($result, $expected);
    }

    public function testIsActiveTrue(): void
    {
        $sut = m::mock(CommunityLicEntity::class)->makePartial();
        $sut->shouldReceive('getStatus->getId')
            ->andReturn(CommunityLicEntity::STATUS_ACTIVE);

        $this->assertTrue($sut->isActive());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsActiveFalse')]
    public function testIsActiveFalse(mixed $status): void
    {
        $sut = m::mock(CommunityLicEntity::class)->makePartial();
        $sut->shouldReceive('getStatus->getId')
            ->andReturn($status);

        $this->assertFalse($sut->isActive());
    }

    public static function dpTestIsActiveFalse(): array
    {
        return [
            [CommunityLicEntity::STATUS_PENDING],
            [CommunityLicEntity::STATUS_EXPIRED],
            [CommunityLicEntity::STATUS_WITHDRAWN],
            [CommunityLicEntity::STATUS_SUSPENDED],
            [CommunityLicEntity::STATUS_ANNUL],
            [CommunityLicEntity::STATUS_RETURNDED],
        ];
    }
}
