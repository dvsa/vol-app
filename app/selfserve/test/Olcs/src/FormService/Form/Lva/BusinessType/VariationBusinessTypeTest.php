<?php

/**
 * Variation Business Type Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva\BusinessType;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\BusinessType\VariationBusinessType;
use Common\FormService\FormServiceInterface;
use Zend\Form\Form;
use Zend\Form\Element;

/**
 * Variation Business Type Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationBusinessTypeTest extends MockeryTestCase
{
    /**
     * @var VariationBusinessType
     */
    protected $sut;

    protected $fsm;

    protected $fh;

    public function setUp()
    {
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();
        $this->fh = m::mock(FormHelperService::class)->makePartial();

        $this->sut = new VariationBusinessType();
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

        $mockVariation = m::mock(FormServiceInterface::class);
        $mockVariation->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $this->fsm->setService('lva-variation', $mockVariation);

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
