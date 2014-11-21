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
        $this->fieldValues = $this->params()->fromFiles();
        $form = $this->generateFormWithData('EbsrPackUpload', 'processSave');

        return $this->getView(['form' => $form]);
    }

    public function processSave($data)
    {
        $dataService = $this->getEbsrDataService();
        $result = $dataService->processPackUpload($data);

        if (is_array($result)) {
            $packs = $result['valid'] + $result['errors'];

            $message = sprintf('%d %s successfully submitted for processing', $packs, ($packs > 1)? ' packs': ' pack');

            $validMessage = sprintf(
                '<br />%d %s validated successfully',
                $result['valid'],
                ($result['valid'] > 1)? 'packs' : 'pack'
            );

            $errorMessage = sprintf(
                '<br />%d %s contained errors',
                $result['errors'],
                ($result['errors'] > 1)? ' packs': ' pack'
            );

            $this->addSuccessMessage(
                $message .
                ($result['valid'] ? $validMessage : '') .
                ($result['errors'] ? $errorMessage : '')
            );

            foreach ($result['messages'] as $pack => $errors) {
                $this->addErrorMessage($pack . ': ' . implode(' ', $errors));
            }
        } else {
            $this->addErrorMessage($result);
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
}
