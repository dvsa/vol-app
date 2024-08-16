<?php

namespace Olcs\Controller\Lva;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Application\SubmitApplication;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Internal Submit Controller
 *
 * @author Alex Peshkov <alex.peshkov@vltech.co.uk>
 */
abstract class AbstractSubmitController extends AbstractApplicationDecisionController
{
    protected $cancelMessageKey  =  'application-not-submitted';
    protected $successMessageKey =  'application-submitted-successfully';
    protected $titleKey          =  'internal-application-submit-title';

    /**
     * @param NiTextTranslation           $niTextTranslationUtil
     * @param AuthorizationService        $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param TranslationHelperService    $translationHelper
     * @param FormHelperService           $formHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        TranslationHelperService $translationHelper,
        protected FormHelperService $formHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService, $flashMessengerHelper, $translationHelper);
    }

    /**
     * getForm
     *
     * @return \Common\Form\Form
     */
    protected function getForm()
    {
        $request  = $this->getRequest();
        $form = $this->formHelper->createFormWithRequest('GenericConfirmation', $request);

        // override default label on confirm action button
        $form->get('messages')->get('message')->setValue('internal-application-submit-confirm');

        return $form;
    }

    /**
     * processDecision
     *
     * @param int   $id   id
     * @param array $data data
     *
     * @return \Common\Service\Cqrs\Response
     */
    protected function processDecision($id, $data)
    {
        return $this->handleCommand(
            SubmitApplication::create(
                [
                    'id' => $id,
                ]
            )
        );
    }
}
