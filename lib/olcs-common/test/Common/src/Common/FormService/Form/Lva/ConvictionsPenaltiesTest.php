<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Common\Form\Elements\InputFilters\ActionLink;
use Common\Form\Model\Form\Lva\Fieldset\ConvictionsPenaltiesData;
use Common\FormService\Form\Lva\ConvictionsPenalties;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Hamcrest\Matchers;
use Laminas\Form\Element;
use Laminas\Form\Element\Radio;
use Laminas\Form\ElementInterface;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Mockery as m;

class ConvictionsPenaltiesTest extends AbstractLvaFormServiceTestCase
{
    public $translator;
    public $urlHelper;
    protected $classToTest = ConvictionsPenalties::class;

    protected $formName = 'Lva\ConvictionsPenalties';

    /** @var  m\MockInterface|\Laminas\Form\Form */
    private $mockedForm;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslationHelperService::class);
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->mockedForm = m::mock(Form::class);
        $this->classArgs = [$this->translator, $this->urlHelper];
        parent::setUp();
    }


    /**
     * @psalm-param '/guides/convictions-and-penalties-guidance-gb/'|'/guides/convictions-and-penalties-guidance-ni/' $guidePath
     * @psalm-param 'convictions-and-penalties-guidance-gb'|'convictions-and-penalties-guidance-ni' $guideName
     */
    public function checkGetForm(string $guidePath, string $guideName): void
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
            ->andReturn($guidePath);

        $dataTable = m::mock(ConvictionsPenaltiesData::class);
        $dataTable
            ->shouldReceive('add')
            ->with(
                Matchers::anInstanceOf(Element::class),
                Matchers::hasKeyValuePair('priority', Matchers::integerValue())
            );

        $ConvictionsReadMoreLink = m::mock(ElementInterface::class);
        $ConvictionsReadMoreLink
            ->shouldReceive('get')
            ->with('readMoreLink')->andReturn(
                m::mock(ActionLink::class)->shouldReceive('setValue')->with($guidePath)->getMock()
            )->getMock();

        $this->mockedForm
            ->shouldReceive('get')->with('data')->andReturn($dataTable)
            ->shouldReceive('get')->with('convictionsReadMoreLink')->andReturn(
                $ConvictionsReadMoreLink
            )->getMock();

        $this->formHelper
            ->shouldReceive('createForm')
            ->once()
            ->with($this->formName)
            ->andReturn($this->mockedForm);

        $actual = $this->sut->getForm();

        $this->assertSame($this->mockedForm, $actual);
    }

    public function checkGetFormNi(): void
    {
        $this->checkGetForm(
            '/guides/convictions-and-penalties-guidance-ni/',
            'convictions-and-penalties-guidance-ni'
        );
    }

    public function checkGetFormGb(): void
    {
        $this->checkGetForm(
            '/guides/convictions-and-penalties-guidance-gb/',
            'convictions-and-penalties-guidance-gb'
        );
    }

    #[\Override]
    public function testGetForm(): void
    {
        $this->checkGetFormGb();
        $this->checkGetFormNi();
    }

    public function testAlterFormDoesNothingIfParamsNotSet(): void
    {
        $this->mockedForm->shouldNotReceive('get');
        $this->sut->changeFormForDirectorVariation($this->mockedForm, []);
    }

    public function testAlterFormChangesLabelsForDirectorVariationType(): void
    {
        $heading = 'selfserve-app-subSection-previous-history-criminal-conviction-hasConv';
        $this->mockedForm->shouldReceive('get')->with('data')->andReturn(
            m::mock(Fieldset::class)
                ->shouldReceive('get')
                ->with('question')
                ->andReturn(
                    m::mock(Radio::class)
                        ->shouldReceive('setLabel')
                        ->with('')->getMock()
                )->getMock()
                ->shouldReceive('getLabel')
                ->andReturn($heading)
                ->getMock()
                ->shouldReceive('setLabel')->with($heading . '-' . RefData::ORG_TYPE_RC . "-dc")
                ->shouldReceive('getAttribute')->with('class')->andReturn('')
                ->shouldReceive('setAttribute')->with('class', ' five-eights')
                ->getMock()
                ->shouldReceive('getAttribute')->with('class')->andReturn('')
                ->shouldReceive('setAttribute')
                ->with('class', ' director-change')
                ->getMock()
        )->getMock();
        $this->mockedForm->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock(Fieldset::class)
                    ->shouldReceive('get')
                    ->with('saveAndContinue')->andReturn(
                        m::mock(Element::class)->shouldReceive('setLabel')
                            ->with('Submit details')->getMock()
                    )->getMock()
            )->getMock();

        $this->mockedForm->shouldReceive('remove')->with('convictionsConfirmation')
            ->getMock();

        $this->formHelper
            ->shouldReceive('remove')
            ->with($this->mockedForm, 'form-actions->save')
            ->once()
            ->getMock();

        $params['variationType'] = RefData::VARIATION_TYPE_DIRECTOR_CHANGE;
        $params['organisationType'] = RefData::ORG_TYPE_RC;
        $this->sut->changeFormForDirectorVariation($this->mockedForm, $params);
    }

    public function testDirectChangeFalseIfNoParam(): void
    {
        $this->assertFalse($this->sut->isDirectorChange([]));
    }

    public function testDirectChangeFalseIfNotAppropriateParam(): void
    {
        $this->assertFalse($this->sut->isDirectorChange(['variationType' => 'inappropriate']));
    }

    public function testDirectChangeTrueIfNotAppropriateVariationType(): void
    {
        $this->assertTrue($this->sut->isDirectorChange(['variationType' => RefData::VARIATION_TYPE_DIRECTOR_CHANGE]));
    }
}
