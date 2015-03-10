<?php

namespace Olcs\Controller\Ebsr;

use Common\Controller\AbstractActionController;

/**
 * Class UploadsController
 */
class UploadsController extends AbstractActionController
{
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
        $this->fieldValues = $this->params()->fromFiles();
        $postFields = $this->params()->fromPost('fields');
        $this->fieldValues['fields']['submissionType'] = $postFields['submissionType'];

        $form = $this->generateFormWithData('EbsrPackUpload', 'processSave');

        return $this->getView(['form' => $form]);
    }

    /**
     * @param array $data
     * @return void
     */
    public function processSave($data)
    {
        $dataService = $this->getEbsrService();

        $result = $dataService->processPackUpload($data, $data['fields']['submissionType']);

        if (isset($result['success'])) {
            $this->addSuccessMessage($result['success']);
        }

        if (isset($result['errors'])) {
            foreach ((array) $result['errors'] as $message) {
                $this->addErrorMessage($message);
            }
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
