<?php

declare(strict_types=1);

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
final class IrhpPermitTypeEntityTest extends EntityTester
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

    #[\Override]
    public function setUp(): void
    {
        $this->sut = m::mock(Entity::class)->makePartial();
    }

    public function testGetCalculatedBundleValues(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsEcmtAnnual')]
    public function testIsEcmtAnnual(mixed $id, mixed $expected): void
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isEcmtAnnual());
    }

    public static function dpIsEcmtAnnual(): \Iterator
    {
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT, true];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, false];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsEcmtShortTerm')]
    public function testIsEcmtShortTerm(mixed $id, mixed $expected): void
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isEcmtShortTerm());
    }

    public static function dpIsEcmtShortTerm(): \Iterator
    {
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, true];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, false];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsEcmtRemoval')]
    public function testIsEcmtRemoval(mixed $id, mixed $expected): void
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isEcmtRemoval());
    }

    public static function dpIsEcmtRemoval(): \Iterator
    {
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, true];
        yield [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, false];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsBilateral')]
    public function testIsBilateral(mixed $id, mixed $expected): void
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isBilateral());
    }

    public static function dpIsBilateral(): \Iterator
    {
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, true];
        yield [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, false];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsMultilateral')]
    public function testIsMultilateral(mixed $id, mixed $expected): void
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isMultilateral());
    }

    public static function dpIsMultilateral(): \Iterator
    {
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, true];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, false];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsMultiStock')]
    public function testIsMultiStock(mixed $id, mixed $expected): void
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isMultiStock());
    }

    public static function dpIsMultiStock(): \Iterator
    {
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, true];
        yield [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, true];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, false];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsApplicationPathEnabled')]
    public function testIsApplicationPathEnabled(mixed $id, mixed $expected): void
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isApplicationPathEnabled());
    }

    public static function dpIsApplicationPathEnabled(): \Iterator
    {
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT, true];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, true];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, true];
        yield [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, true];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, true];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGenerateExpiryDate')]
    public function testGenerateExpiryDate(
        mixed $isEcmtRemoval,
        mixed $isBilateral,
        mixed $isEcmtShortTerm,
        mixed $issueDateString,
        mixed $expectedExpiryDateString
    ): void {
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

    public static function dpGenerateExpiryDate(): \Iterator
    {
        yield [true, false, false, '2019-04-15', '2020-04-14'];
        yield [true, false, false, '2019-05-01', '2020-04-30'];
        yield [true, false, false, '2019-01-01', '2019-12-31'];
        yield [false, true, false, '2019-04-15', '2019-07-15'];
        yield [false, true, false, '2019-12-01', '2020-03-01'];
        yield [false, true, false, '2019-12-31', '2020-03-31'];
        yield [false, false, true, '2019-04-15', '2019-05-15'];
        yield [false, false, true, '2019-12-01', '2019-12-31'];
        yield [false, false, true, '2019-12-31', '2020-01-30'];
    }

    public function testGenerateExpiryDateException(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsCertificateOfRoadworthiness')]
    public function testIsCertificateOfRoadworthiness(mixed $id, mixed $expected): void
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isCertificateOfRoadworthiness());
    }

    public static function dpIsCertificateOfRoadworthiness(): \Iterator
    {
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, true];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, true];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpUsesMultiStockLicenceBehaviour')]
    public function testUsesMultiStockLicenceBehaviour(
        mixed $isMultiStock,
        mixed $isEcmtRemoval,
        mixed $isCertificateOfRoadworthiness,
        mixed $expected
    ): void {
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

    public static function dpUsesMultiStockLicenceBehaviour(): \Iterator
    {
        yield [false, false, false, false];
        yield [false, false, true, true];
        yield [false, true, false, true];
        yield [false, true, true, true];
        yield [true, false, false, true];
        yield [true, false, true, true];
        yield [true, true, false, true];
        yield [true, true, true, true];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsConstrainedCountriesType')]
    public function testIsConstrainedCountriesType(mixed $id, mixed $expected): void
    {
        $this->sut->setId($id);

        $this->assertEquals($expected, $this->sut->isConstrainedCountriesType());
    }

    public static function dpIsConstrainedCountriesType(): \Iterator
    {
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT, true];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, true];
        yield [Entity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_BILATERAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_MULTILATERAL, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE, false];
        yield [Entity::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER, false];
    }
}
