<?php

/**
 * Licence Business Type Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva\BusinessType;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
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

    protected $sm;

    protected $fsm;

    protected $fh;

    public function setUp()
    {
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new LicenceBusinessType();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setFormServiceLocator($this->fsm);
        $this->sut->setFormHelper($this->fh);
    }

    /**
     * @dataProvider trueFalse
     */
    public function testGetForm($bool)
    {
        $mockElement = m::mock(Element::class);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get->get')
            ->with('type')
            ->andReturn($mockElement);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\BusinessType')
            ->andReturn($mockForm)
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockElement, 'business-type.locked')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'data->type');

        $mockLicence = m::mock(FormServiceInterface::class);
        $mockLicence->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

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
