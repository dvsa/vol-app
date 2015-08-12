<?php

/**
 * Document Upload Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentLinks;
use Zend\View\Model\ViewModel;
use Common\Service\File\Exception as FileException;

/**
 * Document Upload Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentFinaliseController extends AbstractDocumentController
{
    private $redirect;

    public function finaliseAction()
    {
        $routeParams = $this->params()->fromRoute();

        if ($this->isButtonPressed('back')) {
            return $this->redirectToDocumentRoute($routeParams['type'], 'generate', $routeParams);
        }

        if ($this->isButtonPressed('cancelFinalise')) {
            return $this->redirect()->toRoute(null, ['action' => 'cancel'], [], true);
        }

        $data = $this->fetchDocData();

        $category = $data['data']['category']['description'];
        $documentSubCategory = $data['data']['subCategory']['subCategoryName'];
        $templateName = $data['data']['template']['description'];

        $uriPattern = $this->getServiceLocator()->get('Config')['document_share']['uri_pattern'];

        $url = str_replace('/', '\\', sprintf($uriPattern, 'documents/' . $data['data']['identifier']));

        $link = sprintf('<a href="%s" target="blank">%s</a>', $url, $templateName);

        $data = [
            'category'    => $category,
            'subCategory' => $documentSubCategory,
            'template'    => $link
        ];

        $this->setEnabledCsrf(false);
        $form = $this->generateFormWithData('FinaliseDocument', 'processSaveLetter', $data);

        if ($this->redirect !== null) {
            return $this->redirect;
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');
        return $this->renderView($view, 'Amend letter');
    }

    public function cancelAction()
    {
        if ($this->getRequest()->isPost()) {

            if ($this->isButtonPressed('yes')) {
                $this->removeDocument($this->params('id'));
                return $this->handleRedirectToDocumentRoute(true);
            }

            return $this->redirect()->toRoute(null, ['action' => null], [], true);
        }

        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('ConfirmAbortLetterGeneration', $this->getRequest());

        $view = new ViewModel();

        $view->setVariable('form', $form);
        $view->setVariable('label', 'Are you sure you want to abort the letter generation?');
        $view->setTemplate('partials/confirm');

        return $this->renderView($view, 'Abort letter generation');
    }

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

        // Update Document Record
        $dto = UpdateDocumentLinks::create($data);
        $response = $this->handleCommand($dto);

        if (!$response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addUnknownError();
            $this->redirect = $this->redirect()->refresh();
            return;
        }

        $this->redirect = $this->handleRedirectToDocumentRoute($this->getRequest()->isXmlHttpRequest());
    }

    protected function handleRedirectToDocumentRoute($ajax = false)
    {
        $routeParams = $this->params()->fromRoute();
        $type = $routeParams['type'];

        return $this->redirectToDocumentRoute($type, null, $routeParams, $ajax);
    }
}
