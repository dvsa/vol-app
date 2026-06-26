<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Common\Form\Elements\InputFilters\SingleCheckbox;
use Common\Form\Elements\Types\HtmlTranslated;
use Common\Form\Form;
use Common\FormService\Form\Lva\FinancialHistory;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\ElementInterface;
use Laminas\Form\Fieldset;
use Laminas\Http\Request;

class FinancialHistoryTest extends MockeryTestCase
{
    public $translator;
    /** @var  FinancialHistory */
    protected $sut;

    /** @var FormHelperService|m\Mock */
    protected $formHelper;

    /** @var FormServiceManager|m\Mock */
    protected $fsm;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->translator = m::mock(TranslationHelperService::class);
        $this->fsm = m::mock(FormServiceManager::class)->makePartial();

        $this->sut = new FinancialHistory($this->formHelper, $this->translator);
    }

    public function testGetForm(): void
    {
        /** @var Request|m\Mock $request */
        $request = m::mock(Request::class);

        $mockForm = m::mock(Form::class);

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\FinancialHistory', $request)
            ->andReturn($mockForm);

        $form = $this->sut->getForm($request, []);

        $this->assertSame($mockForm, $form);
    }

    /**
     * @dataProvider lvaDataProvider
     *
     * @param $lva
     */
    public function testGetFormWithNiFlagSetToY($lva): void
    {
        /** @var Request|m\Mock $request */
        $request = m::mock(Request::class);

        // Mocks
        $mockConfirmationLabel = m::mock(SingleCheckbox::class);
        $mockConfirmationLabel->shouldReceive('setLabel')
            ->with('application_previous-history_financial-history.insolvencyConfirmation.title.ni')
            ->andReturnSelf();

        $mockDataFieldset = m::mock(Fieldset::class);
        $mockDataFieldset->shouldReceive('get')->with('financialHistoryConfirmation')->andReturn(
            m::mock(ElementInterface::class)->shouldReceive('get')->with('insolvencyConfirmation')->andReturn($mockConfirmationLabel)
                ->getMock()
        );

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')
            ->with('data')
            ->andReturn($mockDataFieldset);

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\FinancialHistory', $request)
            ->andReturn($mockForm);

        $form = $this->sut->getForm(
            $request,
            [
                'lva' => $lva,
                'niFlag' => 'Y',
            ]
        );

        $this->assertSame($mockForm, $form);
    }

    /**
     * @return array
     */
    public function lvaDataProvider()
    {
        return [
            ['variation'],
            ['application'],
        ];
    }

    /**
     * @dataProvider provideDirectorChangeWordingVariations
     *
     * @param string $organisationType one of RefData::ORG_TYPE_*
     * @param string $personDescription the type of person ("Person", "Director", "Partner")
     */
    public function testGetFormForDirectorChange($organisationType, $personDescription): void
    {
        /** @var Request|m\Mock $request */
        $request = m::mock(Request::class);

        /** @var Form|m\Mock $mockForm */
        $mockForm = m::mock(Form::class);

        /** @var Fieldset|m\Mock $mockDataFieldset */
        $mockDataFieldset = m::mock(Fieldset::class);

        /** @var HtmlTranslated|m\Mock $mockHasAnyPersonElement */
        $mockHasAnyPersonElement = m::mock(ElementInterface::class);

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\FinancialHistory', $request)
            ->andReturn($mockForm);

        $this->formHelper->shouldReceive('remove')->once()->with($mockForm, 'data->financeHint');
        $this->formHelper->shouldReceive('remove')->once()->with($mockForm, 'data->financialHistoryConfirmation');

        $mockForm->shouldReceive('get')->with('data')->andReturn($mockDataFieldset);
        $mockDataFieldset->shouldReceive('get')->with('hasAnyPerson')->andReturn($mockHasAnyPersonElement);
        $mockHasAnyPersonElement
            ->shouldReceive('setTokens')
            ->with([[sprintf('Has the new %s been:', $personDescription)]])
            ->once();

        $this->translator->shouldReceive('translate')->andReturn([sprintf('Has the new %s been:', $personDescription)]);

        $form = $this->sut->getForm(
            $request,
            ['variationType' => RefData::VARIATION_TYPE_DIRECTOR_CHANGE, 'organisationType' => $organisationType]
        );

        $this->assertSame($mockForm, $form);
    }

    /**
     * @return string[][]
     *
     * @psalm-return list{list{'org_t_rc', 'director'}, list{'org_t_st', 'person'}, list{'org_t_llp', 'partner'}, list{'org_t_p', 'partner'}, list{'org_t_pa', 'person'}, list{'org_t_ir', 'person'}, list{'anything-else', 'person'}}
     */
    public function provideDirectorChangeWordingVariations(): array
    {
        return [
            [RefData::ORG_TYPE_REGISTERED_COMPANY, 'director'],
            [RefData::ORG_TYPE_SOLE_TRADER, 'person'],
            [RefData::ORG_TYPE_LLP, 'partner'],
            [RefData::ORG_TYPE_PARTNERSHIP, 'partner'],
            [RefData::ORG_TYPE_OTHER, 'person'],
            [RefData::ORG_TYPE_IRFO, 'person'],
            ['anything-else', 'person'],
        ];
    }
}
