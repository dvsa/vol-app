<?php

namespace Admin\Controller;

use Admin\Data\Mapper\FeeRate as FeeRateMapper;
use Admin\Form\Model\Form\FeeRate;
use Admin\Form\Model\Form\FeeRateFilter;
use Dvsa\Olcs\Transfer\Command\FeeType\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Query\Fee\FeeType as ItemDTO;
use Dvsa\Olcs\Transfer\Query\FeeType\GetList as ListDTO;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class FeeRateController extends AbstractInternalController implements LeftViewProvider
{
    protected $tableName = 'admin-fee-rates';
    protected $hasMultiDelete = false;

    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;

    protected $formClass = FeeRate::class;
    protected $mapperClass = FeeRateMapper::class;
    protected $filterForm = FeeRateFilter::class;

    protected $updateCommand = UpdateDto::class;

    protected $editContentTitle = 'Edit Fee Rate';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    /**
     * Left View setting
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-fee-rates',
                'navigationTitle' => 'Fee Rates'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }
}
