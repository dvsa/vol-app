<?php

/**
 * Application Business Details Form Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
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

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService')->makePartial();
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = new ApplicationBusinessDetails();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->fsm);
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
