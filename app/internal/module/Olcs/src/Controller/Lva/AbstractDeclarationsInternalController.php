<?php

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Application\UpdateAuthSignature;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Application Undertakings Controller
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
abstract class AbstractDeclarationsInternalController extends AbstractController implements
    ApplicationControllerInterface
{
    /**
     * @param NiTextTranslation           $niTextTranslationUtil
     * @param AuthorizationService        $authService
     * @param FormServiceManager          $formServiceManager
     * @param TranslationHelperService    $translationHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FormServiceManager $formServiceManager,
        protected TranslationHelperService $translationHelper,
        protected FlashMessengerHelperService $flashMessengerHelper
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
        $request = $this->getRequest();

        $form = $this->getForm();
        $this->alterFormForLva($form);

        if ($request->isPost()) {
            $data = (array)$request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $formData = $form->getData();
                $response = $this->handleCommand(
                    UpdateAuthSignature::create(
                        [
                            'id' => $this->getApplicationId(),
                            'version' => $formData['version'],
                            'authSignature' => $formData['declarations']['declarationConfirmation'],
                        ]
                    )
                );
                if ($response->isOk()) {
                    return $this->completeSection('undertakings');
                } else {
                    $this->flashMessengerHelper->addErrorMessage('unknown-error');
                }
            }
        } else {
            $applicationData = $this->getApplicationData($this->getApplicationId());
            $formData = [
                'version' => $applicationData['version'],
                'declarations' => [
                    'declarationConfirmation' => $applicationData['authSignature'] ? 'Y' : 'N'
                ],
            ];
            $form->setData($formData);
        }

        return $this->render('undertakings', $form);
    }

    /**
     * Get the Form
     *
     * @return \Laminas\Form\Form
     */
    protected function getForm()
    {
        $form = $this->formServiceManager
            ->get('lva-' . $this->lva . '-undertakings')
            ->getForm();

        // populate the link
        $translator = $this->translationHelper;
        $summaryDownload = $translator->translateReplace(
            'undertakings_summary_download',
            [
                $this->url()->fromRoute('lva-' . $this->lva . '/review', [], [], true),
                $translator->translate('view-full-application'),
            ]
        );
        $form->get('declarations')->get('summaryDownload')->setAttribute('value', $summaryDownload);

        return $form;
    }
}
