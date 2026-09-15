<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\OtherLicence;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence as Entity;
use Mockery as m;

/**
 * OtherLicence Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class OtherLicenceEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Test update other licence with valid data
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('validDataProvider')]
    public function testUpdateOtherLicenceValid(
        mixed $previousLicenceType,
        mixed $licNo,
        mixed $holderName,
        mixed $willSurrender = null,
        mixed $disqualificationDate = null,
        mixed $disqualificationLength = null,
        mixed $purchaseDate = null
    ): void {
        /** @var m\Mock|Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getPreviousLicenceType')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn($previousLicenceType)
                ->getMock()
            )
            ->getMock();

        $result = $sut->updateOtherLicence(
            $licNo,
            $holderName,
            $willSurrender,
            $disqualificationDate,
            $disqualificationLength,
            $purchaseDate
        );

        $this->assertTrue($result);

        $this->assertSame($licNo, $sut->getLicNo());
        $this->assertSame($holderName, $sut->getHolderName());
        $this->assertSame($willSurrender, $sut->getWillSurrender());
        if ($disqualificationDate === null) {
            $this->assertNull($sut->getDisqualificationDate());
        } else {
            $this->assertEquals(new \DateTime($disqualificationDate), $sut->getDisqualificationDate());
        }
        $this->assertSame($disqualificationLength, $sut->getDisqualificationLength());

        if ($purchaseDate === null) {
            $this->assertNull($sut->getPurchaseDate());
        } else {
            $this->assertEquals(new \DateTime($purchaseDate), $sut->getPurchaseDate());
        }
    }

    /**
     * Test update other licence with invalid data
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidDataProvider')]
    public function testUpdateOtherLicenceInvalid(
        mixed $previousLicenceType,
        mixed $licNo,
        mixed $holderName,
        mixed $willSurrender,
        mixed $disqualificationDate,
        mixed $disqualificationLength,
        mixed $purchaseDate
    ): void {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        if (!$previousLicenceType) {
            $mockPreviousLicenceType = null;
        } else {
            $mockPreviousLicenceType = m::mock()
                ->shouldReceive('getId')
                ->andReturn($previousLicenceType)
                ->getMock();
        }
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getPreviousLicenceType')
            ->andReturn($mockPreviousLicenceType)
            ->getMock();

        $sut->updateOtherLicence(
            $licNo,
            $holderName,
            $willSurrender,
            $disqualificationDate,
            $disqualificationLength,
            $purchaseDate
        );
    }

    public static function validDataProvider(): \Iterator
    {
        yield [Entity::TYPE_CURRENT, 'licNo', 'holderName', 'Y'];
        yield [Entity::TYPE_APPLIED, 'licNo', 'holderName'];
        yield [Entity::TYPE_REFUSED, 'licNo', 'holderName'];
        yield [Entity::TYPE_REVOKED, 'licNo', 'holderName'];
        yield [Entity::TYPE_PUBLIC_INQUIRY, 'licNo', 'holderName'];
        yield [Entity::TYPE_DISQUALIFIED, 'licNo', 'holderName', null, '2015-01-01', '2'];
        yield [Entity::TYPE_HELD, 'licNo', 'holderName', null, null, null, '2014-01-01'];
    }

    public static function invalidDataProvider(): \Iterator
    {
        // field is required
        yield [Entity::TYPE_CURRENT, null, 'holderName', 'Y', '2015-01-01', 2, '2014-01-01'];
        // field is required
        yield [Entity::TYPE_DISQUALIFIED, '123', 'holderName', 'Y', '', 2, '2014-01-01'];
        // date is in future
        yield [Entity::TYPE_DISQUALIFIED, '123', 'holderName', 'Y', new DateTime('now')->modify('+1 day')->format('y-m-d') , 2, '2014-01-01'];
        // empty previous licence type
        yield [null, '123', 'holderName', 'Y', '2019-12-31', 2, '2014-01-01'];
        // wrong previous licence type
        yield ['foo', '123', 'holderName', 'Y', '2019-12-31', 2, '2014-01-01'];
    }

    public function testUpdateOtherLicenceForTml(): void
    {
        $sut = m::mock(Entity::class)->makePartial();
        $sut->updateOtherLicenceForTml('role', 'tml', 'hpw', 'ln', 'oc', 'tav');
        $this->assertEquals('role', $sut->getRole());
        $this->assertEquals('tml', $sut->getTransportManagerLicence());
        $this->assertEquals('hpw', $sut->getHoursPerWeek());
        $this->assertEquals('ln', $sut->getLicNo());
        $this->assertEquals('oc', $sut->getOperatingCentres());
        $this->assertEquals('tav', $sut->getTotalAuthVehicles());
    }

    public function testGetRelatedOrganisationWithNoApplication(): void
    {
        $sut = new Entity();

        $this->assertNull($sut->getRelatedOrganisation());
    }

    public function testGetRelatedOrganisationWithApplication(): void
    {
        $sut = new Entity();

        $mockApplication = m::mock();
        $mockApplication->shouldReceive('getLicence')
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('getOrganisation')
                ->andReturn('ORG1')
                ->once()
                ->getMock()
            )
            ->getMock();
        $sut->setApplication($mockApplication);

        $this->assertSame('ORG1', $sut->getRelatedOrganisation());
    }

    public function testGetRelatedOrganisationWithTmLicence(): void
    {
        $sut = new Entity();

        $mockTmLicence = m::mock()
            ->shouldReceive('getLicence')
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('getOrganisation')
                ->once()
                ->andReturn('ORG1')
                ->getMock()
            )
            ->getMock();
        $sut->setTransportManagerLicence($mockTmLicence);

        $this->assertSame('ORG1', $sut->getRelatedOrganisation());
    }

    public function testGetRelatedOrganisationWithTmApplication(): void
    {
        $sut = new Entity();

        $mockTmApplication = m::mock()
            ->shouldReceive('getApplication')
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('getLicence')
                ->once()
                ->andReturn(
                    m::mock()
                        ->shouldReceive('getOrganisation')
                        ->once()
                        ->andReturn('ORG1')
                        ->getMock()
                )
                ->getMock()
            )
            ->getMock();
        $sut->setTransportManagerApplication($mockTmApplication);

        $this->assertSame('ORG1', $sut->getRelatedOrganisation());
    }

    public function testGetRelatedOrganisationWithTransportManager(): void
    {
        $sut = new Entity();

        $mockTma1 = m::mock()
            ->shouldReceive('getApplication')
            ->once()
            ->andReturn(
                m::mock()
                    ->shouldReceive('getLicence')
                    ->once()
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getOrganisation')
                            ->once()
                            ->andReturn('ORG1')
                            ->getMock()
                    )
                    ->getMock()
            )
            ->getMock();

        $mockTma2 = m::mock()
            ->shouldReceive('getApplication')
            ->once()
            ->andReturn(
                m::mock()
                    ->shouldReceive('getLicence')
                    ->once()
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getOrganisation')
                            ->once()
                            ->andReturn('ORG2')
                            ->getMock()
                    )
                    ->getMock()
            )
            ->getMock();

        $mockTransportManager = m::mock()
            ->shouldReceive('getTmApplications')
            ->once()
            ->andReturn([$mockTma1, $mockTma2])
            ->getMock();

        $sut->setTransportManager($mockTransportManager);

        $this->assertSame(['ORG1', 'ORG2'], $sut->getRelatedOrganisation());
    }
}
