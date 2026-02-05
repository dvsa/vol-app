<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\BusReg;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Publication\Context\BusReg\VariationReasons;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class VariationReasonsTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class VariationReasonsTest extends MockeryTestCase
{
    /**
     * Tests bus reg variation reasons filter
     *
     *
     * @param string $expectedString
     */
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('dpProvideDataProvider')]
    public function testProvide(ArrayCollection $variationReasons, mixed $expectedString): void
    {
        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getVariationReasons')->andReturn($variationReasons);

        $publication = m::mock(PublicationLink::class);
        $publication->shouldReceive('getBusReg')->andReturn($busReg);

        $sut = new VariationReasons(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        $output = [
            'variationReasons' => $expectedString
        ];

        $expectedOutput = new \ArrayObject($output);

        $this->assertEquals($expectedOutput, $sut->provide($publication, new \ArrayObject()));
    }

    public static function dpProvideDataProvider(): array
    {
        $reason1 = 'reason 1';
        $reason2 = 'reason 2';
        $reason3 = 'reason 3';

        $variationReason1 = new RefData();
        $variationReason1->setDescription($reason1);

        $variationReason2 = new RefData();
        $variationReason2->setDescription($reason2);

        $variationReason3 = new RefData();
        $variationReason3->setDescription($reason3);

        $noVariationReason = new ArrayCollection();
        $singleVariationReason = new ArrayCollection([$variationReason1]);
        $threeVariationReasons = new ArrayCollection([$variationReason1, $variationReason2, $variationReason3]);

        return [
            [$noVariationReason, null],
            [$singleVariationReason, 'reason 1'],
            [$threeVariationReasons, 'reason 1, reason 2 and reason 3']
        ];
    }
}
