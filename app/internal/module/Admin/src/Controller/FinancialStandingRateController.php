<?php

/**
 * Financial Standing Rate Controller
 */
namespace Admin\Controller;

use Admin\Form\Model\Form\FinancialStandingRate as FormClass;
use Dvsa\Olcs\Transfer\Command\System\CreateFinancialStandingRate as CreateDto;
use Dvsa\Olcs\Transfer\Command\System\UpdateFinancialStandingRate as UpdateDto;
use Dvsa\Olcs\Transfer\Command\System\DeleteFinancialStandingRateList as DeleteDto;
use Dvsa\Olcs\Transfer\Query\System\FinancialStandingRate as ItemDto;
use Dvsa\Olcs\Transfer\Query\System\FinancialStandingRateList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Data\Mapper\FinancialStandingRate as Mapper;
use Zend\View\Model\ViewModel;

/**
 * Financial Standing Rate Controller
 */
class FinancialStandingRateController extends AbstractInternalController implements PageLayoutProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-financial-standing';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    // list
    protected $tableName = 'admin-financial-standing';
    protected $defaultTableSortField = 'effectiveFrom';
    protected $listDto = ListDto::class;

    // add/edit
    protected $itemDto = ItemDto::class;
    protected $itemParams = ['id'];
    protected $formClass = FormClass::class;
    protected $addFormClass = FormClass::class;
    protected $mapperClass = Mapper::class;
    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;

    // delete
    protected $deleteParams = ['ids' => 'id'];
    protected $deleteCommand = DeleteDto::class;
    protected $hasMultiDelete = true;
    protected $deleteModalTitle = 'Delete rate(s)';
    protected $deleteConfirmMessage = 'Are you sure you want to permanently delete the selected rate(s)?';
    protected $deleteSuccessMessage = 'Rate(s) deleted';


    public function getPageLayout()
    {
        return 'layout/admin-layout';
    }

    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Financial standing rates');

        return parent::indexAction();
    }
}
