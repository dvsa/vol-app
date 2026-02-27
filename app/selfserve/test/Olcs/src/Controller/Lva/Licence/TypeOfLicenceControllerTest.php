<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva\Licence;

use Common\Controller\Lva\Adapters\LicenceLvaAdapter;
use Common\Controller\Lva\Licence\AbstractTypeOfLicenceController;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Licence\TypeOfLicenceController;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Test Licence Type Of Licence Controller
 */
class TypeOfLicenceControllerTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /**
     * Tests index details action for licence entity Non Partner
     */
    public function testCannotUpdateLicenceTypeReturnsMessageToChangeToVariation(): void
    {
        $this->assertInstanceOf(AbstractTypeOfLicenceController::class, new TypeOfLicenceController(
            m::mock(NiTextTranslation::class)->makePartial(),
            m::mock(AuthorizationService::class)->makePartial(),
            m::mock(FlashMessengerHelperService::class)->makePartial(),
            m::mock(ScriptFactory::class)->makePartial(),
            m::mock(FormServiceManager::class)->makePartial(),
            m::mock(VariationLvaService::class)->makePartial(),
            m::mock(LicenceLvaAdapter::class)->makePartial()
        ));
    }
}
