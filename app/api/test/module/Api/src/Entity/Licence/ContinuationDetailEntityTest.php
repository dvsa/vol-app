<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Licence;

use Dvsa\Olcs\Api\Entity\DigitalSignature;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as Entity;
use Mockery as m;

/**
 * ContinuationDetail Entity Unit Tests
 */
class ContinuationDetailEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testUpdateDigitalSignature(): void
    {
        $signatureType = m::mock(RefData::class);
        $signature = m::mock(DigitalSignature::class);

        $sut = m::mock(Entity::class)->makePartial();

        //this will be set to true later
        $this->assertNotTrue($sut->getIsDigital());

        $sut->updateDigitalSignature($signatureType, $signature);
        $this->assertEquals($signatureType, $sut->getSignatureType());
        $this->assertEquals($signature, $sut->getDigitalSignature());
        $this->assertTrue($sut->getIsDigital());
    }

    public function testGetRelatedOrganisation(): void
    {
        /** @var Entity $continuationDetail */
        $continuationDetail = m::mock(Entity::class)->makePartial();
        $continuationDetail->shouldReceive('getLicence->getOrganisation')->andReturn('ORG');

        $this->assertEquals('ORG', $continuationDetail->getRelatedOrganisation());
    }

    public static function dpGetAmountDeclaredDataProvider(): array
    {
        return [
            [0.00, null, null, null, null],
            [0.00, 0, 0, 0, 0],
            [10.00, 1, 2, 3, 4],
            [10.10, 1.01, 2.02, 3.03, 4.04],
            [1.01, 1.01, null, null, null],
            [1.01, null, 1.01, null, null],
            [1.01, null, null, 1.01, null],
            [1.01, null, null, null, 1.01],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetAmountDeclaredDataProvider')]
    public function testGetAmountDeclared(
        mixed $expected,
        mixed $averageBalanceAmount,
        mixed $overdraftAmount,
        mixed $factoringAmount,
        mixed $otherFinancesAmount
    ): void {
        $continuationDetail = new Entity();
        $continuationDetail->setAverageBalanceAmount($averageBalanceAmount);
        $continuationDetail->setOverdraftAmount($overdraftAmount);
        $continuationDetail->setFactoringAmount($factoringAmount);
        $continuationDetail->setOtherFinancesAmount($otherFinancesAmount);

        $this->assertEqualsWithDelta($expected, $continuationDetail->getAmountDeclared(), 0.01);
    }

    public function testGetContextValue(): void
    {
        $continuationDetail = new Entity();
        $continuationDetail->setId(87);

        $this->assertSame(87, $continuationDetail->getContextValue());
    }
}
