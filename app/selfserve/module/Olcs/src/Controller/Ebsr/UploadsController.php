<?php

namespace Olcs\Controller\Ebsr;

use Common\Controller\Traits\GenericMethods;
use Common\Controller\Traits\GenericRenderView;
use Common\Util\FlashMessengerTrait;
use \Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;

/**
 * Class UploadsController
 */
class UploadsController extends ZendAbstractActionController
{
    use GenericMethods,
        GenericRenderView,
        FlashMessengerTrait;

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->getServiceLocator()->get('Table');
        $dataService = $this->getEbsrDataService();

        $table = $tableBuilder->buildTable(
            'ebsr-packs',
            $dataService->fetchList(),
            ['url' => $this->plugin('url')],
            false
        );

        return $this->getView(['table' => $table]);
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function uploadAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirect()->toRoute('bus-registration/ebsr', ['action' => 'upload']);
        }

        $fieldValues = $this->params()->fromFiles();
        $postFields = $this->params()->fromPost('fields');
        $fieldValues['fields']['submissionType'] = $postFields['submissionType'];

        $form = $this->generateFormWithData('EbsrPackUpload', 'processSave', null, false, true, $fieldValues);

        return $this->getView(['form' => $form]);
    }

    /**
     * @param array $data
     * @return void
     */
    public function processSave($data, $form = null, $additionalParams = null)
    {
        $dataService = $this->getEbsrService();

        $result = $dataService->processPackUpload($data, $data['fields']['submissionType']);

        if (isset($result['success'])) {
            $this->addSuccessMessage($result['success']);
        }
        if (isset($result['errors'])) {
            $messages['fields']['file'] = [];
            foreach ((array) $result['errors'] as $message) {
                array_push($messages['fields']['file'], $message);
            }
            $form->setMessages($messages);
        }
    }

    /**
     * @return \Olcs\Service\Data\EbsrPack
     */
    public function getEbsrDataService()
    {
        /** @var \Olcs\Service\Data\EbsrPack $dataService */
        $dataService = $this->getServiceLocator()->get('DataServiceManager')->get('Olcs\Service\Data\EbsrPack');
        return $dataService;
    }

    /**
     * @return \Olcs\Service\Ebsr
     */
    public function getEbsrService()
    {
        /** @var \Olcs\Service\Ebsr $dataService */
        $dataService = $this->getServiceLocator()->get('Olcs\Service\Ebsr');
        return $dataService;
    }
}
