<?php

namespace Admin\Controller;

use Admin\Data\Mapper\IrhpPermitStock as PermitStockMapper;
use Admin\Form\Model\Form\IrhpPermitStock as PermitStockForm;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\IrhpPermitStock\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitStock\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitStock\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\GetList as ListDto;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

/**
 * IRHP Permits Admin Controller
 */
class IrhpPermitStockController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-permits';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'addAction' => ['forms/irhp-permit-stock-modal'],
        'editAction' => ['forms/irhp-permit-stock-modal'],
    ];

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableName = 'admin-irhp-permit-stock';
    protected $defaultTableSortField = 'validFrom';
    protected $defaultTableOrderField = 'DESC';
    protected $listDto = ListDto::class;

    protected $itemDto = ItemDto::class;
    protected $formClass = PermitStockForm::class;
    protected $addFormClass = PermitStockForm::class;
    protected $mapperClass = PermitStockMapper::class;
    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;

    protected $deleteCommand = DeleteDto::class;
    protected $hasMultiDelete = false;
    protected $deleteModalTitle = 'Remove IRHP Permit Stock';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this permit stock?';
    protected $deleteSuccessMessage = 'The permit stock has been removed';

    protected $addContentTitle = 'Add permit stock';

    protected $tableViewTemplate = 'pages/irhp-permit-stock/index';

    public function __construct(
        TranslationHelperService $translationHelperService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelperService,
        Navigation $navigation,
        private ScriptFactory $scriptFactory
    ) {
        parent::__construct($translationHelperService, $formHelper, $flashMessengerHelperService, $navigation);
    }
    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-permits',
                'navigationTitle' => 'Permits system settings',
                'singleNav' => true
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Permit Stock Index View
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $this->scriptFactory->loadFile('irhp-permit-stock');
        $this->placeholder()->setPlaceholder('pageTitle', 'Permits');

        return parent::indexAction();
    }

    /**
     * Setup required values for Add form
     *
     * @param                                         $form
     * @param                                         $formData
     * @return                                        mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function alterFormForAdd($form, $formData)
    {
        $fieldset = $form->get('permitStockDetails');
        $fieldset->remove('applicationPathGroupHtml');
        $fieldset->remove('businessProcessHtml');

        return $form;
    }

    /**
     * Setup required values for Edit form
     *
     * @param  $form
     * @param  $formData
     * @return mixed
     */
    protected function alterFormForEdit($form, $formData)
    {
        $fieldset = $form->get('permitStockDetails');
        $fieldset->remove('applicationPathGroup');
        $fieldset->remove('businessProcess');

        $fieldset->get('applicationPathGroupHtml')
            ->setValue($formData['permitStockDetails']['applicationPathGroup']['name'] ?? '');
        $fieldset->get('businessProcessHtml')
            ->setValue($formData['permitStockDetails']['businessProcess']['description'] ?? '');

        return $form;
    }
}
