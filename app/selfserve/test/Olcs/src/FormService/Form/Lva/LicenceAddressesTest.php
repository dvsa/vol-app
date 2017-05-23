<?php

namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\Addresses\LicenceAddresses;
use Common\Service\Entity\LicenceEntityService;
use Zend\Form\Form;

/**
 * Licence Addresses Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceAddressesTest extends MockeryTestCase
{
    protected $sut;

    protected $fsm;

    protected $formHelper;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService')->makePartial();
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = new LicenceAddresses();
        $this->sut->setFormServiceLocator($this->fsm);
        $this->sut->setFormHelper($this->formHelper);
    }

    public function testGetForm()
    {
        $mockAddresses = m::mock('\Common\FormService\FormServiceInterface');
        $this->fsm->setService('lva-licence-addresses', $mockAddresses);

        $formActions = m::mock();
        $formActions->shouldReceive('get')
            ->with('save')
            ->andReturn(
                m::mock()
                ->shouldReceive('setAttribute')
                ->with('class', 'action--primary large')
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper
            ->shouldReceive('createForm')
            ->with('Lva\Addresses')
            ->andReturn($mockForm)
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->cancel')
            ->once()
            ->getMock();

        $this->sut->getForm(
            ['typeOfLicence' => ['licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL]]
        );
    }
}
