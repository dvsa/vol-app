<?php

namespace CommonTest\Service\Qa;

use Common\Service\Qa\FieldsetPopulatorInterface;
use Common\Service\Qa\FieldsetPopulatorProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * FieldsetPopulatorProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class FieldsetPopulatorProviderTest extends MockeryTestCase
{
    private $sut;

    private $populator2;

    #[\Override]
    protected function setUp(): void
    {
        $this->populator2 = m::mock(FieldsetPopulatorInterface::class);

        $this->sut = new FieldsetPopulatorProvider();
        $this->sut->registerPopulator('type1', m::mock(FieldsetPopulatorInterface::class));
        $this->sut->registerPopulator('type2', $this->populator2);
        $this->sut->registerPopulator('type3', m::mock(FieldsetPopulatorInterface::class));
    }

    public function testGet(): void
    {
        $this->assertSame(
            $this->populator2,
            $this->sut->get('type2')
        );
    }

    public function testExceptionOnUnknownType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fieldset populator not found: type4');

        $this->sut->get('type4');
    }
}
