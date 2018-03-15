<?php

/**
 * Convictions & Penalties Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\FormService\Form\Lva;

use Common\Form\Elements\InputFilters\ActionLink;
use Common\Form\Model\Form\Lva\Fieldset\ConvictionsPenaltiesReadMoreLink;
use Common\Service\Helper\TranslationHelperService;
use Olcs\FormService\Form\Lva\ConvictionsPenalties;
use Mockery as m;
use Zend\Di\ServiceLocator;

/**
 * Convictions & Penalties Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ConvictionsPenaltiesTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = ConvictionsPenalties::class;

    public function testGetForm()
    {
        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);

        $this->formHelper->shouldReceive('createForm')
            ->andReturn($mockForm);

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

        $ConvictionsReadMoreLink = m::mock(ConvictionsPenaltiesReadMoreLink::class);
        $ConvictionsReadMoreLink
            ->shouldReceive('get')
            ->with('readMoreLink')->andReturn(
                m::mock(ActionLink::class)->shouldReceive('setValue')->once()->with('dummy-url')->getMock()
            )->getMock();

        $mockForm
            ->shouldReceive('get')->with('convictionsReadMoreLink')->andReturn(
                $ConvictionsReadMoreLink
            )->getMock();

        $translator = m::mock(TranslationHelperService::class);
        $translator
            ->shouldReceive('translate')
            ->with('convictions-and-penalties-guidance-route-param')
            ->andReturn('dummy-translated-param');

        $mockServiceLocator = m::mock(ServiceLocator::class);

        $mockUrl = m::mock();
        $mockUrl
            ->shouldReceive('fromRoute')
            ->with(
                'guides/guide',
                ['guide' => 'dummy-translated-param']
            )
            ->once()
            ->andReturn('dummy-url');

        $mockServiceLocator->shouldReceive('get')->with('Helper\Translation')->once()->andReturn($translator);
        $mockServiceLocator->shouldReceive('get')->with('Helper\Url')->once()->andReturn($mockUrl);

        $this->fsm
            ->shouldReceive('getServiceLocator')
            ->andReturn($mockServiceLocator);

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
