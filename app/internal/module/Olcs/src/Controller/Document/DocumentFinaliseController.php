<?php

/**
 * Document Upload Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

use Dvsa\Olcs\Transfer\Command\Document\PrintLetter;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentLinks;
use Zend\View\Model\ViewModel;

/**
 * Document Upload Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentFinaliseController extends AbstractDocumentController
{
    private $redirect;

    /**
     * finalise Action
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function finaliseAction()
    {
        $routeParams = $this->params()->fromRoute();

        if ($this->isButtonPressed('back')) {
            return $this->redirectToDocumentRoute($routeParams['type'], 'generate', $routeParams);
        }

        if ($this->isButtonPressed('cancelFinalise')) {
            return $this->redirect()->toRoute(
                null, ['action' => 'cancel'], ['query' => $this->getRequest()->getQuery()->toArray()], true
            );
        }

        $data = $this->fetchDocData();

        $category = $data['data']['category']['description'];
        $documentSubCategory = $data['data']['subCategory']['subCategoryName'];
        $templateName = $data['data']['template']['description'];

        $uriPattern = $this->getServiceLocator()->get('Config')['document_share']['uri_pattern'];

        $url = sprintf($uriPattern, $data['data']['identifier']);

        $link = sprintf('<a href="%s" data-file-url="%s" target="blank">%s</a>', $url, $url, $templateName);

        $data = [
            'category'    => $category,
            'subCategory' => $documentSubCategory,
            'template'    => $link
        ];

        $form = $this->generateFormWithData('FinaliseDocument', 'processSaveLetter', $data, false, false);

        if ($this->redirect !== null) {
            return $this->redirect;
        }

        $this->getServiceLocator()->get('Script')->loadFile('file-link');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');
        return $this->renderView($view, 'Amend letter');
    }

    /**
     * get method form class
     * @NOTE Slightly extends the AbstractActionController version of this method, so that we can disable the security
     * element. Updating this in the AbstractActionController causes lots of tests to fail, but shouldn't technically
     * break anything.
     *
     * @param string $type type
     *
     * @return \Zend\Form\Form
     */
    protected function getFormClass($type)
    {
        return $this->getServiceLocator()->get('Helper\Form')->createForm(
            $this->normaliseFormName($type, true),
            $this->getEnabledCsrf()
        );
    }

    /**
     * print Action
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function printAction()
    {
        $id = $this->params('doc');

        $data = [
            'id' => $id
        ];

        if ($this->getRequest()->isPost()) {
            $data['shouldEmail'] = $this->isButtonPressed('yes') ? 'Y' : 'N';
        }

        $response = $this->handleCommand(PrintLetter::create($data));

        if ($response->isOk()) {
            return $this->handleRedirectToDocumentRoute($this->getRequest()->isXmlHttpRequest());
        }

        if ($response->isClientError() && isset($response->getResult()['messages']['should_email'])) {
            $form = $this->getServiceLocator()->get('Helper\Form')
                ->createFormWithRequest('ConfirmYesNo', $this->getRequest());

            $view = new ViewModel();

            $view->setVariable('form', $form);
            $view->setVariable('label', 'Would you like to email the document to the operator?');
            $view->setTemplate('pages/confirm');

            return $this->renderView($view, 'Send letter by email');
        }

        $this->getServiceLocator()->get('Helper\FlashMessenger')->addUnknownError();

        return $this->redirect()->toRoute(
            null, ['action' => 'finalise'], ['query' => $this->getRequest()->getQuery()->toArray()], true
        );
    }

    /**
     * Cancel Action
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function cancelAction()
    {
        if ($this->getRequest()->isPost()) {

            if ($this->isButtonPressed('yes')) {

                $this->removeDocument($this->params('doc'));
                return $this->handleRedirectToDocumentRoute(true);
            }

            return $this->redirect()->toRoute(
                null, ['action' => null], ['query' => $this->getRequest()->getQuery()->toArray()], true
            );
        }

        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('ConfirmYesNo', $this->getRequest());

        $view = new ViewModel();

        $view->setVariable('form', $form);
        $view->setVariable('label', 'Are you sure you want to abort the letter generation?');
        $view->setTemplate('pages/confirm');

        return $this->renderView($view, 'Abort letter generation');
    }

    /**
     * Process Save Letter
     *
     * @param array $data data
     *
     * @return void|\Zend\Http\Response
     */
    public function processSaveLetter($data)
    {
        $routeParams = $this->params()->fromRoute();

        $type = $routeParams['type'];

        $data = [
            'id' => $this->params('doc')
        ];

        // we need to link certain documents to multiple IDs
        switch ($type) {
            case 'application':
                $data['licence'] = $this->getLicenceIdForApplication();
                break;

            case 'case':
                $data = array_merge($data, $this->getCaseData());
                break;

            case 'busReg':
                $data['licence'] = $this->params('licence');
                break;

            default:
                break;
        }

        $data[$type] = $routeParams[$this->getRouteParamKeyForType($type)];

        // if both the entityType and the entityId has some values then add it into $data
        if (!empty($routeParams['entityType']) && !empty($routeParams['entityId'])) {
            $data[$routeParams['entityType']] = $routeParams['entityId'];
        }

        // Update Document Record
        $dto = UpdateDocumentLinks::create($data);
        $response = $this->handleCommand($dto);

        if (!$response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addUnknownError();
            $this->redirect = $this->redirect()->refresh();
            return;
        }

        return $this->redirect()->toRoute(
            null, ['action' => 'print'], ['query' => $this->getRequest()->getQuery()->toArray()], true
        );
    }

    /**
     * handle Redirect To Document Route
     *
     * @param bool $ajax ajax
     *
     * @return \Zend\Http\Response
     */
    protected function handleRedirectToDocumentRoute($ajax = false)
    {
        $routeParams = $this->params()->fromRoute();
        $type = $routeParams['type'];

        return $this->redirectToDocumentRoute($type, null, $routeParams, $ajax);
    }
}
