<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\UrlHelperService;
use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\ApplicationConvictionsPenalties;
use Laminas\Form\Form;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;
use Common\Form\Model\Form\Lva\Fieldset\ConvictionsPenaltiesData;
use Common\Service\Helper\TranslationHelperService;
use Common\Form\Elements\InputFilters\ActionLink;
use Laminas\Form\Element;

class ApplicationConvictionsPenaltiesTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationConvictionsPenalties
     */
    protected $sut;

    protected $fh;

    protected $formName = 'Lva\ConvictionsPenalties';

    private $urlHelper;

    private $translator;

    private $formHelper;

    public function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class)->makePartial();
        $this->translator = m::mock(TranslationHelperService::class);
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new ApplicationConvictionsPenalties($this->formHelper, $this->translator, $this->urlHelper);
    }

    /**
     * @psalm-param '/guides/convictions-and-penalties-guidance-gb/'|'/guides/convictions-and-penalties-guidance-ni/' $guidePath
     * @psalm-param 'convictions-and-penalties-guidance-gb'|'convictions-and-penalties-guidance-ni' $guideName
     */
    public function checkAlterForm(string $guidePath, string $guideName): void
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

        $ConvictionsReadMoreLink = m::mock(ElementInterface::class);
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

    public function checkGetFormNi(): void
    {
        $this->checkAlterForm(
            '/guides/convictions-and-penalties-guidance-ni/',
            'convictions-and-penalties-guidance-ni'
        );
    }

    public function checkGetFormGb(): void
    {
        $this->checkAlterForm(
            '/guides/convictions-and-penalties-guidance-gb/',
            'convictions-and-penalties-guidance-gb'
        );
    }

    public function testAlterForm(): void
    {
        $this->checkGetFormGb();
        $this->setUp();
        $this->checkGetFormNi();
    }
}
