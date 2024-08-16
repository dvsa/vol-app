<?php

/**
 * Can Access Variation With Variation Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessVariationWithVariation;

/**
 * Can Access Variation With Variation Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessVariationWithVariationTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessVariationWithVariation
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessVariationWithVariation();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getVariation')->andReturn(111);

        $this->setIsValid('canAccessApplication', [111], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function provider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
