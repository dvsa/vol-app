<?php

/**
 * Rule Admin Controller
 */
namespace Admin\Controller\DataRetention;

use Dvsa\Olcs\Transfer\Query\DataRetention\RuleAdmin as ListDto;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\AbstractInternalController;
use Dvsa\Olcs\Transfer\Command\DataRetention\UpdateRule as UpdateDto;
use Olcs\Data\Mapper\DataRetentionRule as Mapper;
use Zend\View\Model\ViewModel;
use Admin\Form\Model\Form\DataRetentionAdmin as FormClass;
use Dvsa\Olcs\Transfer\Query\DataRetention\GetRule as ItemDto;

/**
 * Rule admin controller
 */
class RuleAdminController extends AbstractInternalController implements LeftViewProvider
{

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-data-retention';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    // list
    protected $defaultTableSortField = 'id';
    protected $defaultTableOrderField = 'DESC';
    protected $listDto = ListDto::class;
    protected $tableName = 'admin-data-retention-rules-admin';

    // edit
    protected $itemDto = ItemDto::class;
    protected $itemParams = ['id'];
    protected $formClass = FormClass::class;
    protected $mapperClass = Mapper::class;
    protected $updateCommand = UpdateDto::class;

    protected $editContentTitle = 'Edit Data retention rule';

    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-data-retention',
                'navigationTitle' => 'Data retention'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Data retention rules');

        return parent::indexAction();
    }
}
