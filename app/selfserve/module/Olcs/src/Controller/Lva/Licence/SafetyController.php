<?php

/**
 * External Licence Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Common\Controller\Lva\Adapters\LicenceLvaAdapter;
use Common\Controller\Lva\Traits\LicenceSafetyControllerTrait;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * External Licence Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyController extends Lva\AbstractSafetyController
{
    use LicenceSafetyControllerTrait, LicenceControllerTrait {
        LicenceSafetyControllerTrait::alterFormForLva as licenceSafetyAlterFormForLva;
        LicenceControllerTrait::alterFormForLva as licenceAlterFormForLva;
    }

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FormServiceManager $formServiceManager
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param TableFactory $tableFactory
     * @param ScriptFactory $scriptFactory
     * @param TranslationHelperService $translationHelper
     * @param LicenceLvaAdapter $lvaAdapter
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FormServiceManager $formServiceManager,
        FlashMessengerHelperService $flashMessengerHelper,
        TableFactory $tableFactory,
        ScriptFactory $scriptFactory,
        TranslationHelperService $translationHelper,
        protected LicenceLvaAdapter $lvaAdapter
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $flashMessengerHelper,
            $tableFactory,
            $scriptFactory,
            $translationHelper
        );
    }

    protected $lva = 'licence';
    protected string $location = 'external';

    /**
     * This method allows both trait alterFormForLva methods to be called
     *
     * @param Form $form Form to alter
     * @param null $data Required for compatability with parent method signature
     *
     * @return void
     */
    protected function alterFormForLva(Form $form, $data = null)
    {
        $this->licenceAlterFormForLva($form);
        $this->licenceSafetyAlterFormForLva($form);
    }
}
