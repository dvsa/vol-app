<?php

/**
 * Variation Type Of Licence Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\FormService\Form\Lva;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Olcs\FormService\Form\Lva\VariationTypeOfLicence;
use Zend\Form\Element;

/**
 * Variation Type Of Licence Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class VariationTypeOfLicenceTest extends AbstractLvaFormServiceTestCase
{
    /** @var FormHelperService|\Mockery\MockInterface  */
    protected $formHelper;
    /** @var FormServiceManager|\Mockery\MockInterface  */
    protected $fsm;
    /** @var VariationTypeOfLicence|\Mockery\MockInterface  */
    protected $sut;

    public function setUp()
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->fsm = m::mock(FormServiceManager::class)->makePartial();

        $this->sut = m::mock(VariationTypeOfLicence::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->fsm);
    }


    public function testGetForm()
    {
        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);

        $this->formHelper->shouldReceive('createForm')
            ->andReturn($mockForm);

        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm
            ->shouldReceive('get')
            ->with('form-actions')
            ->once()
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('save')
                    ->once()
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setLabel')
                            ->with('internal.save.button')
                            ->once()
                            ->getMock()
                    )
                    ->getMock()
            )
            ->getMock();

        $licenceType = m::mock(Element\Select::class);
        $mockForm
            ->shouldReceive('get')
            ->with('type-of-licence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('remove')
                    ->with('difference')
                    ->once()
                    ->shouldReceive('get')
                    ->with('licence-type')
                    ->once()
                    ->andReturn($licenceType)
                    ->getMock()
            );

        $this->formHelper
            ->shouldReceive('setCurrentOption')
            ->with($licenceType, 'foo')
            ->once();

        $this->fsm
            ->shouldReceive('get')
            ->with('lva-variation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('alterForm')
                    ->with($mockForm)
                    ->andReturn($mockForm)
                    ->getMock()
            );

        $this->sut->shouldReceive('lockElements');

        $form = $this->sut->getForm(['currentLicenceType' => 'foo']);

        static::assertSame($mockForm, $form);
    }
}
