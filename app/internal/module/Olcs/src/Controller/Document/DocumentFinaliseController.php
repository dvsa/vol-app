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

        $data = $this->fetchDocData();

        $category = $data['data']['category']['description'];
        $documentSubCategory = $data['data']['subCategory']['subCategoryName'];
        $templateName = $data['data']['template']['description'];

        $uriPattern = $this->getServiceLocator()->get('Config')['document_share']['uri_pattern'];

        $url = str_replace('/', '\\', sprintf($uriPattern, $data['data']['identifier']));

        $link = sprintf('<a href="%s" target="blank">%s</a>', $url, $templateName);

        $data = [
            'category'    => $category,
            'subCategory' => $documentSubCategory,
            'template'    => $link
        ];

        $form = $this->generateFormWithData('FinaliseDocument', 'processSaveLetter', $data);

        if ($this->redirect !== null) {
            return $this->redirect;
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');
        return $this->renderView($view, 'Amend letter');
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

        $this->redirect = $this->redirectToDocumentRoute($type, null, $routeParams, $this->getRequest()->isXmlHttpRequest());
    }
}
