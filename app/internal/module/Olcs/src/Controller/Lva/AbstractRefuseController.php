<?php

namespace Olcs\Controller\Lva;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Application\RefuseApplication;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Internal Refuse Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractRefuseController extends AbstractApplicationDecisionController
{
    protected $cancelMessageKey  =  'application-not-refused';
    protected $successMessageKey =  'application-refused-successfully';
    protected $titleKey          =  'internal-application-refuse-title';

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
     * get method form
     *
     * @return \Laminas\Form\Form
     */
    protected function getForm()
    {
        $request  = $this->getRequest();
        $form = $this->formHelper->createFormWithRequest('GenericConfirmation', $request);

        // override default label on confirm action button
        $form->get('messages')->get('message')->setValue('internal-application-refuse-confirm');

        return $form;
    }

    /**
     * Process Decision
     *
     * @param int   $id   id
     * @param array $data data
     *
     * @return \Common\Service\Cqrs\Response
     */
    protected function processDecision($id, $data)
    {
        return $this->handleCommand(
            RefuseApplication::create(
                [
                    'id' => $id,
                ]
            )
        );
    }
}
