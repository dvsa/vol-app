<?php

/**
 * Licence Business Type Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva\BusinessType;

use Common\Form\Elements\InputFilters\Lva\BackToLicenceActionLink;
use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\BusinessType\LicenceBusinessType;
use Common\FormService\FormServiceInterface;
use Zend\Form\Form;
use Zend\Form\Element;

/**
 * Licence Business Type Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceBusinessTypeTest extends MockeryTestCase
{
    /**
     * @var LicenceBusinessType
     */
    protected $sut;

    protected $fsm;

    protected $fh;

    protected $sm;

    public function setUp()
    {
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        $this->sut = new LicenceBusinessType();
        $this->sut->setFormServiceLocator($this->fsm);
        $this->sut->setFormHelper($this->fh);
        $this->fsm->setServiceLocator($this->sm);
    }

    /**
     * @dataProvider trueFalse
     */
    public function testGetForm($bool)
    {
        $mockElement = m::mock(Element::class);

        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);

        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');
        $formActions->shouldReceive('remove')->once()->with('cancel');

        $formActions->shouldReceive('add')->once()->with(m::type(BackToLicenceActionLink::class));

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')->with('data')
            ->andReturn(
                m::mock()->shouldReceive('get')
                    ->with('type')
                    ->andReturn($mockElement)
                    ->getMock()
            );

        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\BusinessType')
            ->andReturn($mockForm)
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockElement, 'business-type.locked')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'data->type')
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions')
            ->once()
            ->getMock();

        $mockLicence = m::mock(FormServiceInterface::class);
        $mockLicence->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $this->sm
            ->shouldReceive('get')
            ->with('Helper\Guidance')
            ->andReturn(
                m::mock()
                    ->shouldReceive('append')
                    ->with('business-type.locked.message')
                    ->once()
                    ->getMock()
            );

        $this->fsm->setService('lva-licence', $mockLicence);

        $form = $this->sut->getForm($bool);

        $this->assertSame($mockForm, $form);
    }

    public function trueFalse()
    {
        return [
            [
                true
            ],
            [
                false
            ]
        ];
    }
}
