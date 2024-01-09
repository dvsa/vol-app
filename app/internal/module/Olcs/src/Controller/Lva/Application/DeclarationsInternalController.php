<?php

namespace Olcs\Controller\Lva\Application;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Olcs\Module;
use ZfcRbac\Service\AuthorizationService;

/**
 * Internal Application Undertakings Controller
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeclarationsInternalController extends \Olcs\Controller\Lva\AbstractDeclarationsInternalController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location = 'internal';
    protected StringHelperService $stringHelper;
    protected RestrictionHelperService $restrictionHelper;

    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormServiceManager $formServiceManager
     * @param TranslationHelperService $translationHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param StringHelperService $stringHelper
     * @param RestrictionHelperService $restrictionHelper
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormServiceManager $formServiceManager,
        TranslationHelperService $translationHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        StringHelperService $stringHelper,
        RestrictionHelperService $restrictionHelper,
        $navigation
    ) {
        $this->stringHelper = $stringHelper;
        $this->restrictionHelper = $restrictionHelper;
        $this->navigation = $navigation;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formServiceManager,
            $translationHelper,
            $flashMessengerHelper
        );
    }

    /**
     * Alter the form
     *
     * @param \Common\Form\Form $form The form
     * @param null              $data Optional array of data
     *
     * @return null
     */
    protected function alterFormForLva(Form $form, $data = null)
    {
        // Get signature data from application declaration DTO
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Application\Declaration::create(['id' => $this->getIdentifier()])
        );
        // If all ok and verify signature exists on the application
        if ($response->isOk() && $response->getResult()['signature']) {
            // Add signature details into form
            $signatureDetails = $response->getResult()['signature'];
            $form->get('declarations')->get('verifySignatureText')->setValue(
                sprintf(
                    'This application has been digitally signed on %s by %s with date of birth %s',
                    (new \DateTime($signatureDetails['date']))->format(Module::$dateFormat),
                    $signatureDetails['name'],
                    (new \DateTime($signatureDetails['dob']))->format(Module::$dateFormat)
                )
            );

            // Change checkbox label
            $form->get('declarations')->get('declarationConfirmation')->setLabel('Signature accepted');
        }

        parent::alterFormForLva($form, $data);
    }
}
