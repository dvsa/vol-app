<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva\Adapters\ApplicationTransportManagerAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Common\Test\MockeryTestCase;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Common\Service\Helper\TransportManagerHelperService;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * @see TransportManagersController
 */
class TransportManagersControllerTest extends MockeryTestCase
{
    /**
     * @var TransportManagersController
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function isInitializedIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable($this->sut->isInitialized(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('isInitializedIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function isInitializedReturnsFalseBeforeCreateServiceIsCalled(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->isInitialized();

        // Assert
        $this->assertFalse($result);
    }

    protected function setUpSut(): void
    {
        $mockNiTextTranslationUtil = m::mock(NiTextTranslation::class)->makePartial();
        $mockAuthService = m::mock(AuthorizationService::class)->makePartial();
        $mockFormHelper = m::mock(FormHelperService::class)->makePartial();
        $mockFormServiceManager = m::mock(FormServiceManager::class)->makePartial();
        $mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class)->makePartial();
        $mockScriptFactory = m::mock(ScriptFactory::class)->makePartial();
        $mockQueryService = m::mock(QueryService::class)->makePartial();
        $mockCommandService = m::mock(CommandService::class)->makePartial();
        $mockTransferAnnotationBuilder = m::mock(AnnotationBuilder::class)->makePartial();
        $mockTransportManagerHelper = m::mock(TransportManagerHelperService::class)->makePartial();
        $mockTranslationHelper = m::mock(TranslationHelperService::class)->makePartial();
        $mockRestrictionHelper = m::mock(RestrictionHelperService::class)->makePartial();
        $mockStringHelper = m::mock(StringHelperService::class)->makePartial();
        $mockLvaAdapter = m::mock(ApplicationTransportManagerAdapter::class)->makePartial();
        $mockTableFactory = m::mock(TableFactory::class)->makePartial();
        $mockUploadHelper = m::mock(FileUploadHelperService::class)->makePartial();

        $this->sut = new TransportManagersController(
            $mockNiTextTranslationUtil,
            $mockAuthService,
            $mockFormHelper,
            $mockFormServiceManager,
            $mockFlashMessengerHelper,
            $mockScriptFactory,
            $mockQueryService,
            $mockCommandService,
            $mockTransferAnnotationBuilder,
            $mockTransportManagerHelper,
            $mockTranslationHelper,
            $mockRestrictionHelper,
            $mockStringHelper,
            $mockLvaAdapter,
            $mockTableFactory,
            $mockUploadHelper
        );
    }
}
