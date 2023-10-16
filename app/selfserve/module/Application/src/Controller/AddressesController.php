<?php

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use ZfcRbac\Service\AuthorizationService;

/**
 * External Application Addresses Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddressesController extends Lva\AbstractAddressesController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location  = 'external';

    protected RestrictionHelperService $restrictionHelper;
    protected StringHelperService $stringHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormServiceManager $formServiceManager
     * @param ScriptFactory $scriptFactory
     * @param RestrictionHelperService $restrictionHelper
     * @param StringHelperService $stringHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        ScriptFactory $scriptFactory,
        RestrictionHelperService $restrictionHelper,
        StringHelperService $stringHelper
    ) {
        $this->restrictionHelper = $restrictionHelper;
        $this->stringHelper = $stringHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory
        );
    }

    /**
     * Alter form
     *
     * @param Form  $form Form
     * @param array $data Api/Form Data
     *
     * @return void
     */
    protected function alterFormForLva(Form $form, $data = null)
    {
        $this->formHelper->remove($form, 'consultant');
        $this->formHelper->remove($form, 'consultantContact');
        $this->formHelper->remove($form, 'consultantAddress');
    }
}
