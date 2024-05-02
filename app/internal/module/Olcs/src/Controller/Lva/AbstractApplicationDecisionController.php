<?php

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Internal Application Decsision Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractApplicationDecisionController extends AbstractController implements
    ApplicationControllerInterface
{
    protected $cancelMessageKey;
    protected $successMessageKey;
    protected $titleKey;

    /**
     * @param NiTextTranslation           $niTextTranslationUtil
     * @param AuthorizationService        $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param TranslationHelperService    $translationHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected TranslationHelperService $translationHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }


    /**
     * indexAction
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function indexAction()
    {
        $helperFlashMsgr = $this->flashMessengerHelper;

        /**
 * @var \Laminas\Http\Request $request
*/
        $request = $this->getRequest();
        $id      = $this->params('application');

        /**
 * @var \Laminas\Form\FormInterface $form
*/
        $form    = $this->getForm();

        if ($request->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                $helperFlashMsgr->addWarningMessage($this->cancelMessageKey);

                return $this->redirectToOverview($id);
            }

            $postData = (array)$request->getPost();
            $form->setData($postData);

            if ($form->isValid()) {
                $data = $form->getData();

                $response = $this->processDecision($id, $data);

                if ($response->isOk()) {
                    $message = $this->translationHelper
                        ->translateReplace($this->successMessageKey, [$id]);

                    $helperFlashMsgr->addSuccessMessage($message);
                } else {
                    $helperFlashMsgr->addErrorMessage('unknown-error');
                }

                return $this->redirectToOverview($id);
            }
        }

        $view = new ViewModel(['title' => $this->titleKey, 'form' => $form]);
        $view->setTemplate('sections/lva/lva-details');

        return $this->render($view);
    }

    /**
     * Process Decision
     *
     * @param int   $id   id
     * @param array $data data
     *
     * @return \Common\Service\Cqrs\Response
     */
    abstract protected function processDecision($id, $data);

    /**
     * getForm
     *
     * @return \Laminas\Form\FormInterface
     */
    abstract protected function getForm();

    /**
     * redirectToOverview
     *
     * @param string $applicationId applicationId
     *
     * @return \Laminas\Http\Response
     */
    protected function redirectToOverview($applicationId)
    {
        return $this->redirect()->toRouteAjax(
            'lva-' . $this->lva . '/overview',
            ['application' => $applicationId]
        );
    }

    /**
     * Check for redirect
     *
     * @param int $lvaId lvaid
     *
     * @return null
     */
    protected function checkForRedirect($lvaId)
    {
        // no-op to avoid LVA predispatch magic kicking in
    }
}
