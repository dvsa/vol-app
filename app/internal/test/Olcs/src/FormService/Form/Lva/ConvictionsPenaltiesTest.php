<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Form\Elements\InputFilters\ActionLink;
use Common\Form\Model\Form\Lva\Fieldset\ConvictionsPenaltiesReadMoreLink;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Laminas\Form\ElementInterface;
use Olcs\FormService\Form\Lva\ConvictionsPenalties;
use Mockery as m;

/**
 * Convictions & Penalties Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ConvictionsPenaltiesTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = ConvictionsPenalties::class;

    public function setUp(): void
    {
        $this->translator = m::mock(TranslationHelperService::class);
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->classArgs = [$this->translator, $this->urlHelper];
        parent::setUp();
    }

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
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('save')
                    ->once()
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setLabel')
                            ->with('internal.save.button')
                            ->once()
                            ->getMock()
                    )
                    ->getMock()
            )
            ->getMock();

        $ConvictionsReadMoreLink = m::mock(ElementInterface::class);
        $ConvictionsReadMoreLink
            ->shouldReceive('get')
            ->with('readMoreLink')->andReturn(
                m::mock(ActionLink::class)->shouldReceive('setValue')->once()->with('dummy-url')->getMock()
            )->getMock();

        $mockForm
            ->shouldReceive('get')->with('convictionsReadMoreLink')->andReturn(
                $ConvictionsReadMoreLink
            )->getMock();

        $this->translator
            ->shouldReceive('translate')
            ->with('convictions-and-penalties-guidance-route-param')
            ->andReturn('dummy-translated-param');

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with(
                'guides/guide',
                ['guide' => 'dummy-translated-param']
            )
            ->once()
            ->andReturn('dummy-url');

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
