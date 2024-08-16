<?php

declare(strict_types=1);

namespace Admin\Controller;

use Admin\Data\Mapper\BusNoticePeriod as BusNoticePeriodMapper;
use Admin\Form\Model\Form\BusNoticePeriod as BusNoticePeriodForm;
use Dvsa\Olcs\Transfer\Command\Bus\CreateNoticePeriod as CreateDTO;
use Dvsa\Olcs\Transfer\Query\Bus\BusNoticePeriodList as ListDTO;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class BusNoticePeriodController extends AbstractInternalController implements LeftViewProvider
{
    protected $tableName = 'admin-bus-notice-period';
    protected $createCommand = CreateDTO::class;
    protected $listDto = ListDto::class;
    protected $formClass = BusNoticePeriodForm::class;
    protected $mapperClass = BusNoticePeriodMapper::class;
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    /**
     * @return ViewModel
     */
    public function getLeftView(): ViewModel
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-bus-registration/notice-period',
                'navigationTitle' => 'Bus Registrations',
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }
}
