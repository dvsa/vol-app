<?php

/**
 * Document Relink Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Olcs\Controller\Document;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Document\CopyDocument;
use Dvsa\Olcs\Transfer\Command\Document\MoveDocument;
use Laminas\Http\Response;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Data\Mapper\DocumentRelink as DocumentRelinkMapper;

class DocumentRelinkController extends AbstractDocumentController
{
    private $labels = [
        'application'      => 'Application ID',
        'busReg'           => 'Bus registaration No',
        'case'             => 'Case ID',
        'licence'          => 'Licence No',
        'irfoOrganisation' => 'IRFO ID',
        'irhpApplication'  => 'IRHP application id',
        'transportManager' => 'Transport manager ID'
    ];

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        array $config,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected TranslationHelperService $translationHelper
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $config
        );
    }

    /**
     * Performs a copy or move document(s) and redirect back to index
     *
     * @return ViewModel
     */
    public function relinkAction()
    {
        $form = $this->getRelinkForm(
            $this->params()->fromRoute('type'),
            $this->params()->fromRoute('doc')
        );

        $post = (array) $this->getRequest()->getPost();

        $form->setData($post);

        $this->alterForm($form);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            $res = $this->processRelink($post, $form);
            if ($res instanceof Response) {
                return $res;
            }
        }

        $this->loadScripts(['forms/relink-document']);

        return $this->getRelinkView($form);
    }

    /**
     * get method Relink View
     *
     * @param string $form form
     *
     * @return ViewModel
     */
    protected function getRelinkView($form)
    {
        $translator = $this->translationHelper;
        $view = new ViewModel(['form' => $form]);

        $view->setTemplate('pages/form');

        return $this->renderView($view, $translator->translate('internal.documents.relink_documents'));
    }

    /**
     * get method relink form
     *
     * @param string $type type
     * @param int    $ids  ids
     *
     * @return \Laminas\Form\Form
     */
    protected function getRelinkForm($type, $ids)
    {
        $form = $this->formHelper
            ->createFormWithRequest('DocumentRelink', $this->getRequest());

        $form->get('document-relink-details')->get('type')->setValue($type);
        $form->get('document-relink-details')->get('ids')->setValue($ids);

        return $form;
    }

    /**
     * process relink
     *
     * @param array              $post post
     * @param \Laminas\Form\Form $form form
     *
     * @return Response|void
     */
    protected function processRelink($post, $form)
    {
        $routeParams = $this->params()->fromRoute();
        $type = $routeParams['type'];

        if (isset($post['form-actions']['copy'])) {
            $dto = CopyDocument::class;
            $message = 'internal.documents.documents_copied';
        } elseif (isset($post['form-actions']['move'])) {
            $dto = MoveDocument::class;
            $message = 'internal.documents.documents_moved';
        } else {
            return $this->redirectToDocumentRoute($type, null, $routeParams, true);
        }

        $data = DocumentRelinkMapper::mapFromForm($post);
        $response = $this->handleCommand($dto::create($data));

        if ($response->isOk()) {
            $this->flashMessengerHelper->addSuccessMessage($message);
            return $this->redirectToDocumentRoute($type, null, $routeParams, true);
        }

        if ($response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        if ($response->isClientError()) {
            $errors = DocumentRelinkMapper::mapFromErrors($form, $response->getResult());

            foreach ($errors as $error) {
                $this->flashMessengerHelper->addErrorMessage($error);
            }
        }
    }

    /**
     * alterForm
     *
     * @param \Laminas\Form\Form $form form
     *
     * @return void
     */
    protected function alterForm($form)
    {
        $type = $form->get('document-relink-details')->get('type')->getValue();

        $form->get('document-relink-details')->get('targetId')->setLabel($this->labels[$type]);
    }
}
