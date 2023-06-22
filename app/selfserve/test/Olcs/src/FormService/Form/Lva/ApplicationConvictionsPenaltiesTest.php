<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\UrlHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\ApplicationConvictionsPenalties;
use Laminas\Form\Form;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;
use Common\Form\Model\Form\Lva\Fieldset\ConvictionsPenaltiesData;
use Common\Service\Helper\TranslationHelperService;
use Common\Form\Model\Form\Lva\Fieldset\ConvictionsPenaltiesReadMoreLink;
use Common\Form\Elements\InputFilters\ActionLink;
use Common\FormService\FormServiceManager;
use Laminas\Form\Element;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Application Convictions and Penalties Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationConvictionsPenaltiesTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationConvictionsPenalties
     */
    protected $sut;

    protected $fh;

    protected $formName = 'Lva\ConvictionsPenalties';

    public function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class)->makePartial();
        $this->translator = m::mock(TranslationHelperService::class);
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new ApplicationConvictionsPenalties($this->formHelper, $this->translator, $this->urlHelper);
    }

    public function checkAlterForm($guidePath, $guideName)
    {
        $this->translator
            ->shouldReceive('translate')
            ->andReturn($guideName);

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with(
                'guides/guide',
                ['guide' => $guideName]
            )
            ->once()
            ->andReturn($guidePath);

        $dataTable = m::mock(ConvictionsPenaltiesData::class);
        $dataTable
            ->shouldReceive('add')
            ->with(
                \Hamcrest\Matchers::anInstanceOf(Element::class),
                \Hamcrest\Matchers::hasKeyValuePair('priority', \Hamcrest\Matchers::integerValue())
            );

        $ConvictionsReadMoreLink = m::mock(ConvictionsPenaltiesReadMoreLink::class);
        $ConvictionsReadMoreLink
            ->shouldReceive('get')
            ->with('readMoreLink')->andReturn(
                m::mock(ActionLink::class)->shouldReceive('setValue')->once()->with($guidePath)->getMock()
            )->getMock();

        $form = m::mock(Form::class);
        $form
            ->shouldReceive('get')->with('data')->andReturn($dataTable)
            ->shouldReceive('get')->with('convictionsReadMoreLink')->andReturn(
                $ConvictionsReadMoreLink
            )->getMock();

        $this->formHelper
            ->shouldReceive('createForm')
            ->once()
            ->with($this->formName)
            ->andReturn($form);

        $this->mockAlterButtons($form, $this->formHelper);

        $actual = $this->sut->getForm();

        $this->assertSame($form, $actual);
    }

    public function checkGetFormNi()
    {
        $this->checkAlterForm(
            '/guides/convictions-and-penalties-guidance-ni/',
            'convictions-and-penalties-guidance-ni'
        );
    }

    public function checkGetFormGb()
    {
        $this->checkAlterForm(
            '/guides/convictions-and-penalties-guidance-gb/',
            'convictions-and-penalties-guidance-gb'
        );
    }

    public function testAlterForm()
    {
        $this->checkGetFormGb();
        $this->setUp();
        $this->checkGetFormNi();
    }
}
