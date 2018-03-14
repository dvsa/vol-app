<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\ApplicationConvictionsPenalties;
use Zend\Form\Form;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;
use Common\Form\Model\Form\Lva\Fieldset\ConvictionsPenaltiesData;
use Common\Service\Helper\TranslationHelperService;
use Common\Form\Model\Form\Lva\Fieldset\ConvictionsPenaltiesReadMoreLink;
use Common\Form\Elements\InputFilters\ActionLink;
use Common\FormService\FormServiceManager;
use Zend\Form\Element;
use Zend\Di\ServiceLocator;

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

    public function setUp()
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->sut = new ApplicationConvictionsPenalties();
        $this->sut->setFormHelper($this->fh);
    }

    public function checkAlterForm($guidePath, $guideName)
    {
        $translator = m::mock(TranslationHelperService::class);
        $translator
            ->shouldReceive('translate')
            ->andReturn($guideName);

        $mockUrl = m::mock();
        $mockUrl
            ->shouldReceive('fromRoute')
            ->with(
                'guides/guide',
                ['guide' => $guideName]
            )
            ->once()
            ->andReturn($guidePath);
        $this->formHelper = m::mock(FormHelperService::class);
        $this->fsm = m::mock(FormServiceManager::class)->makePartial();


        $dataTable = m::mock(ConvictionsPenaltiesData::class);
        $dataTable
            ->shouldReceive('add')
            ->with(anInstanceOf(Element::class), hasKeyValuePair('priority', integerValue()));

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

        $mockServiceLocator = m::mock(ServiceLocator::class);


        $mockServiceLocator->shouldReceive('get')->with('Helper\Translation')->once()->andReturn($translator);
        $mockServiceLocator->shouldReceive('get')->with('Helper\Url')->once()->andReturn($mockUrl);

        $this->formHelper
            ->shouldReceive('createForm')
            ->once()
            ->with($this->formName)
            ->andReturn($form);

        $this->fsm
            ->shouldReceive('getServiceLocator')
            ->andReturn($mockServiceLocator);

        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->fsm);

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
        $this->checkGetFormNi();
    }
}
