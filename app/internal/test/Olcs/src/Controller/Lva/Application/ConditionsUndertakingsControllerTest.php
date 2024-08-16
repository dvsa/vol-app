<?php

namespace OlcsTest\Controller\Lva\Application;

use Common\Controller\Lva\Adapters\ApplicationConditionsUndertakingsAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use Olcs\Controller\Lva\Application\ConditionsUndertakingsController;
use Olcs\Mvc\Controller\Plugin\ScriptFactory;
use Mockery as m;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Class ConditionsUndertakingsControllerTest
 *
 * @package OlcsTest\Controller\Lva\Application
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class ConditionsUndertakingsControllerTest extends AbstractLvaControllerTestCase
{
    protected $sut;

    protected $mockNiTextTranslationUtil;

    protected $mockAuthService;

    protected $mockFormHelper;

    protected $mockFlashMessengerHelper;

    protected $mockFormServiceManager;

    protected $mockTableFactory;

    protected $mockStringHelper;

    protected $mockLvaAdapter;

    protected $mockRestrictionHelper;

    protected $mockNavigation;


    public function setUp(): void
    {
        parent::setUp();

        $this->mockNiTextTranslationUtil = m::mock(NiTextTranslation::class);
        $this->mockAuthService = m::mock(AuthorizationService::class);
        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $this->mockFormServiceManager = m::mock(FormServiceManager::class);
        $this->mockTableFactory = m::mock(TableFactory::class);
        $this->mockStringHelper = m::mock(StringHelperService::class);
        $this->mockLvaAdapter = m::mock(ApplicationConditionsUndertakingsAdapter::class);
        $this->mockRestrictionHelper = m::mock(RestrictionHelperService::class);
        $this->mockNavigation = m::mock('navigation');

        $this->mockController(
            ConditionsUndertakingsController::class,
            [
            $this->mockNiTextTranslationUtil,
            $this->mockAuthService,
            $this->mockFormHelper,
            $this->mockFlashMessengerHelper,
            $this->mockFormServiceManager,
            $this->mockTableFactory,
            $this->mockStringHelper,
            $this->mockLvaAdapter,
            $this->mockRestrictionHelper,
            $this->mockNavigation
            ]
        );

        $this->sut->shouldReceive('setAdapter');
    }

    public function testIndexActionWithGet()
    {
        $mockForm = m::mock(Form::class);

        $mockForm->shouldReceive('get')
            ->with('table')
            ->andReturn('form');

        $this->mockLvaAdapter->shouldReceive('getTableData')
            ->with(7)
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('alterTable')
            ->with(m::mock())
            ->shouldReceive('getTableName')
            ->andReturn('lva-conditions-undertakings')
            ->shouldReceive('attachMainScripts');

        $this->sut->shouldReceive('getForm')
            ->andReturn($mockForm);

        $this->mockRender();

        $this->sut->indexAction();
    }
}
