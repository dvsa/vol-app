<?php

namespace Dvsa\OlcsTest\Api\Entity\Permits;

use DateTime;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as Entity;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPath as ApplicationPathEntity;
use Mockery as m;
use RuntimeException;

/**
 * IrhpPermitType Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class IrhpPermitTypeEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * @var Entity
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(Entity::class)->makePartial();
    }

    public function testGetCalculatedBundleValues()
    {
        $this->sut->shouldReceive('isEcmtAnnual')
            ->once()
            ->withNoArgs()
            ->andReturn(true)
            ->shouldReceive('isEcmtShortTerm')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isEcmtRemoval')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isBilateral')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isMultilateral')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isCertificateOfRoadworthiness')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isApplicationPathEnabled')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $this->assertSame(
            [
                'isEcmtAnnual' => true,
                'isEcmtShortTerm' => false,
                'isEcmtRemoval' => false,
                'isBilateral' => false,
                'isMultilateral' => false,
                'isCertificateOfRoadworthiness' => false,
                'isApplicationPathEnabled' => false,
            ],
            $this->sut->getCalculatedBundleValues()
        );
    }

    /**
    * @dataProvider dpIsEcmtAnnual
    */
    public function testIsEcmtAnnual($id, $expected)
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isEcmtAnnual());
    }

    public function dpIsEcmtAnnual()
    {
        return [
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT, true],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, false],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, false],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, false],
        ];
    }

    /**
    * @dataProvider dpIsEcmtShortTerm
    */
    public function testIsEcmtShortTerm($id, $expected)
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isEcmtShortTerm());
    }

    public function dpIsEcmtShortTerm()
    {
        return [
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT, false],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, true],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, false],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, false],
        ];
    }

    /**
    * @dataProvider dpIsEcmtRemoval
    */
    public function testIsEcmtRemoval($id, $expected)
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isEcmtRemoval());
    }

    public function dpIsEcmtRemoval()
    {
        return [
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT, false],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, false],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, true],
            [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, false],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, false],
        ];
    }

    /**
    * @dataProvider dpIsBilateral
    */
    public function testIsBilateral($id, $expected)
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isBilateral());
    }

    public function dpIsBilateral()
    {
        return [
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT, false],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, false],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, true],
            [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, false],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, false],
        ];
    }

    /**
    * @dataProvider dpIsMultilateral
    */
    public function testIsMultilateral($id, $expected)
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isMultilateral());
    }

    public function dpIsMultilateral()
    {
        return [
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT, false],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, false],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, true],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, false],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, false],
        ];
    }

    /**
     * @dataProvider dpIsMultiStock
     */
    public function testIsMultiStock($id, $expected)
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isMultiStock());
    }

    public function dpIsMultiStock()
    {
        return [
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT, false],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, false],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, true],
            [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, true],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, false],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, false],
        ];
    }

    /**
    * @dataProvider dpIsApplicationPathEnabled
    */
    public function testIsApplicationPathEnabled($id, $expected)
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isApplicationPathEnabled());
    }

    public function dpIsApplicationPathEnabled()
    {
        return [
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT, true],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, true],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, true],
            [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, true],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, true],
        ];
    }

    /**
    * @dataProvider dpGenerateExpiryDate
    */
    public function testGenerateExpiryDate(
        $isEcmtRemoval,
        $isBilateral,
        $isEcmtShortTerm,
        $issueDateString,
        $expectedExpiryDateString
    ) {
        $this->sut->shouldReceive('isEcmtRemoval')
            ->withNoArgs()
            ->andReturn($isEcmtRemoval);
        $this->sut->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturn($isBilateral);
        $this->sut->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn($isEcmtShortTerm);

        $issueDate = new DateTime($issueDateString);
        $expiryDate = $this->sut->generateExpiryDate($issueDate);

        $this->assertNotSame($expiryDate, $issueDate);

        $this->assertEquals(
            $expectedExpiryDateString,
            $expiryDate->format('Y-m-d')
        );
    }

    public function dpGenerateExpiryDate()
    {
        return [
            [true, false, false, '2019-04-15', '2020-04-14'],
            [true, false, false, '2019-05-01', '2020-04-30'],
            [true, false, false, '2019-01-01', '2019-12-31'],
            [false, true, false, '2019-04-15', '2019-07-15'],
            [false, true, false, '2019-12-01', '2020-03-01'],
            [false, true, false, '2019-12-31', '2020-03-31'],
            [false, false, true, '2019-04-15', '2019-05-15'],
            [false, false, true, '2019-12-01', '2019-12-31'],
            [false, false, true, '2019-12-31', '2020-01-30'],
        ];
    }

    public function testGenerateExpiryDateException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to generate an expiry date for permit type 77');

        $this->sut->shouldReceive('isEcmtRemoval')
            ->withNoArgs()
            ->andReturnFalse();
        $this->sut->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->setId(77);
        $this->sut->generateExpiryDate(new DateTime());
    }

    /**
     * @dataProvider dpIsCertificateOfRoadworthiness
     */
    public function testIsCertificateOfRoadworthiness($id, $expected)
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isCertificateOfRoadworthiness());
    }

    public function dpIsCertificateOfRoadworthiness()
    {
        return [
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT, false],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, false],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, true],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, true],
        ];
    }

    /**
     * @dataProvider dpUsesMultiStockLicenceBehaviour
     */
    public function testUsesMultiStockLicenceBehaviour(
        $isMultiStock,
        $isEcmtRemoval,
        $isCertificateOfRoadworthiness,
        $expected
    ) {
        $this->sut->shouldReceive('isMultiStock')
            ->withNoArgs()
            ->andReturn($isMultiStock);

        $this->sut->shouldReceive('isEcmtRemoval')
            ->withNoArgs()
            ->andReturn($isEcmtRemoval);

        $this->sut->shouldReceive('isCertificateOfRoadworthiness')
            ->withNoArgs()
            ->andReturn($isCertificateOfRoadworthiness);

        $this->assertEquals(
            $expected,
            $this->sut->usesMultiStockLicenceBehaviour()
        );
    }

    public function dpUsesMultiStockLicenceBehaviour()
    {
        return [
            [false, false, false, false],
            [false, false, true, true],
            [false, true, false, true],
            [false, true, true, true],
            [true, false, false, true],
            [true, false, true, true],
            [true, true, false, true],
            [true, true, true, true],
        ];
    }

    /**
    * @dataProvider dpIsConstrainedCountriesType
    */
    public function testIsConstrainedCountriesType($id, $expected)
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isConstrainedCountriesType());
    }

    public function dpIsConstrainedCountriesType()
    {
        return [
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT, true],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, true],
            [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, false],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, false],
            [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, false],
        ];
    }
}
