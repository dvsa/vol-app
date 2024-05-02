<?php

namespace Olcs\Controller\Lva;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Application\ReviveApplication;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Class AbstractReviveApplicationController
 *
 * Revive an application.
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
abstract class AbstractReviveApplicationController extends AbstractApplicationDecisionController
{
    protected $lva               = 'application';
    protected string $location          = 'internal';
    protected $cancelMessageKey  = 'application-not-revive-application';
    protected $successMessageKey = 'application-revive-application-successfully';
    protected $titleKey          = 'internal-application-revive-application-title';

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
     * get Form
     *
     * @return \Common\Form\Form
     */
    protected function getForm()
    {
        $request  = $this->getRequest();
        $form = $this->formHelper->createFormWithRequest('GenericConfirmation', $request);

        // override default label on confirm action button
        $form->get('messages')->get('message')->setValue('internal-application-revive-application-confirm');

        return $form;
    }

    /**
     * process Decision
     *
     * @param int   $id   id
     * @param array $data data
     *
     * @return \Common\Service\Cqrs\Response
     */
    protected function processDecision($id, $data)
    {
        return $this->handleCommand(
            ReviveApplication::create(
                [
                    'id' => $id,
                ]
            )
        );
    }
}
