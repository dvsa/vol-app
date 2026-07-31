<?php

declare(strict_types=1);

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
final class ConditionsUndertakingsControllerTest extends AbstractLvaControllerTestCase
{
    protected $sut;

    protected $mockLvaAdapter;


    public function setUp(): void
    {
        parent::setUp();

        $mockNiTextTranslationUtil = m::mock(NiTextTranslation::class);
        $mockAuthService = m::mock(AuthorizationService::class);
        $mockFormHelper = m::mock(FormHelperService::class);
        $mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $mockFormServiceManager = m::mock(FormServiceManager::class);
        $mockTableFactory = m::mock(TableFactory::class);
        $mockStringHelper = m::mock(StringHelperService::class);
        $this->mockLvaAdapter = m::mock(ApplicationConditionsUndertakingsAdapter::class);
        $mockRestrictionHelper = m::mock(RestrictionHelperService::class);
        $mockNavigation = m::mock('navigation');

        $this->mockController(
            ConditionsUndertakingsController::class,
            [
            $mockNiTextTranslationUtil,
            $mockAuthService,
            $mockFormHelper,
            $mockFlashMessengerHelper,
            $mockFormServiceManager,
            $mockTableFactory,
            $mockStringHelper,
            $this->mockLvaAdapter,
            $mockRestrictionHelper,
            $mockNavigation
            ]
        );

        $this->sut->shouldReceive('setAdapter');
    }

    public function testIndexActionWithGet(): void
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
