<?php

namespace Olcs\Controller\Ebsr;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;

/**
 * Class UploadsController
 */
class UploadsController extends AbstractActionController
{
    public function indexAction()
    {
        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->getServiceLocator()->get('Table');

        /** @var \Olcs\Service\Data\EbsrPack $dataService */
        $dataService = $this->getServiceLocator()->get('Olcs\Service\Data\EbsrPack');

        $table = $tableBuilder->buildTable(
            'ebsr-packs',
            $dataService->fetchPackList(),
            ['url' => $this->plugin('url')],
            false
        );

        return $this->getView(['table' => $table]);
    }
}
