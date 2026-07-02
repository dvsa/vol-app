<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Service\Qa\Custom\Common\WarningAdder;
use Common\Service\Qa\Custom\Ecmt\NiWarningConditionalAdder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;

/**
 * NiWarningConditionalAdderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NiWarningConditionalAdderTest extends MockeryTestCase
{
    private $fieldset;

    private $warningAdder;

    private $niWarningConditionalAdder;

    #[\Override]
    protected function setUp(): void
    {
        $this->fieldset = m::mock(Fieldset::class);

        $this->warningAdder = m::mock(WarningAdder::class);

        $this->niWarningConditionalAdder = new NiWarningConditionalAdder($this->warningAdder);
    }

    public function testAddWhenShowNiWarningTrue(): void
    {
        $this->warningAdder->shouldReceive('add')
            ->with(
                $this->fieldset,
                'permits.page.number-of-trips.northern-ireland.warning',
                WarningAdder::DEFAULT_PRIORITY,
                'niWarning'
            )
            ->once();

        $this->niWarningConditionalAdder->addIfRequired($this->fieldset, true);
    }

    public function testDoNothingWhenNiWarningFalse(): void
    {
        $this->warningAdder->shouldReceive('add')
            ->never();

        $this->niWarningConditionalAdder->addIfRequired($this->fieldset, false);
    }
}
