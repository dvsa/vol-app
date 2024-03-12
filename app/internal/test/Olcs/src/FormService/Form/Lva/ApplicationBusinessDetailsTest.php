<?php

namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\ApplicationBusinessDetails;

/**
 * Application Business Details Form Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationBusinessDetailsTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    protected $fsm;

    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class)->makePartial();
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();

        $this->sut = new ApplicationBusinessDetails($this->formHelper, $this->fsm);
    }

    public function testAlterForm()
    {
        $mockForm = m::mock();

        $this->fsm->shouldReceive('get')
            ->with('lva-application')
            ->andReturn(
                m::mock()
                ->shouldReceive('alterForm')
                ->with($mockForm)
                ->once()
                ->getMock()
            );

        $this->formHelper->shouldReceive('remove')
            ->with($mockForm, 'allow-email')
            ->once();

        $this->sut->alterForm($mockForm, ['orgType' => 'foo']);
    }
}
