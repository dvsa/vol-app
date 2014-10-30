<?php

namespace Olcs\Controller\Ebsr;

use Common\Controller\AbstractActionController;

/**
 * Class UploadsController
 */
class UploadsController extends AbstractActionController
{
    public function indexAction()
    {
        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->getServiceLocator()->get('Table');
        $dataService = $this->getEbsrDataService();

        $table = $tableBuilder->buildTable(
            'ebsr-packs',
            $dataService->fetchPackList(),
            ['url' => $this->plugin('url')],
            false
        );

        return $this->getView(['table' => $table]);
    }

    public function uploadAction()
    {
        $form = $this->generateFormWithData('EbsrPackUpload', 'processSave');

        return $this->getView(['form' => $form]);
    }

    public function processSave($data)
    {
        $dataService = $this->getEbsrDataService();
        $validPacks = $dataService->processPackUpload($data['validData']);

        if ($validPacks) {
            $this->addSuccessMessage($validPacks . 'successfully submitted for processing');
            $this->redirectToIndex();
        } else {
            $this->addErrorMessage(
                'No valid packs were found in your upload, please verify your file and try again'
            );
        }
    }

    /**
     * @return \Olcs\Service\Data\EbsrPack
     */
    public function getEbsrDataService()
    {
        /** @var \Olcs\Service\Data\EbsrPack $dataService */
        $dataService = $this->getServiceLocator()->get('Olcs\Service\Data\EbsrPack');
        return $dataService;
    }
}
