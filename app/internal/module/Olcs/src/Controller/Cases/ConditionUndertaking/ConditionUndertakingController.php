<?php

namespace Olcs\Controller\Cases\ConditionUndertaking;

use Common\Exception\DataServiceException;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\CasesWithLicence as CasesWithLicenceDto;
use Dvsa\Olcs\Transfer\Query\Cases\ConditionUndertaking\ConditionUndertakingList as ListDto;
use Dvsa\Olcs\Transfer\Query\ConditionUndertaking\Get as ItemDto;
use Laminas\Form\FormInterface;
use Laminas\Navigation\Navigation;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;

/**
 * Case ConditionUndertaking Controller
 *
 * @to-do We need to extract the logic from the various LVA adapters and replicate it in a new way. This is to
 * alter the form and generate the value options for the attachedTo field which has 2 group options:
 *
 * Licence
 *     OB12345
 * OCs
 *     <oc address>
 *     <oc address>
 *
 * UPDATE: LVA adapters have been removed and hence this form is broken. I *think* the attachedTo field on the form
 * used to call an LVA service to generate the drop down. That service needs to call
 * backend/licence/operatingcentre/<licence_id> to get a list of oc's against the licence and return the group options
 * (see Mat Evans for more info)
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ConditionUndertakingController extends AbstractInternalController implements
    CaseControllerInterface,
    LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_details_conditions_undertakings';

    protected $routeIdentifier = 'id';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'condition';
    protected $listDto = ListDto::class;
    protected $listVars = ['case'];
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        Navigation $navigation,
        protected HelperPluginManager $viewHelperManager
    ) {

        parent::__construct($translationHelper, $formHelper, $flashMessengerHelper, $navigation);
    }
    /**
     * get method for left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/cases/partials/left');

        return $view;
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = null;
    protected $detailsViewPlaceholderName = null;
    protected $itemDto = ItemDto::class;

    protected $itemParams = ['case', 'id'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = 'ConditionUndertaking';
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = \Olcs\Data\Mapper\ConditionUndertaking::class;
    protected $addContentTitle = 'Add condition or undertaking';
    protected $editContentTitle = 'Edit condition or undertaking';

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $createCommand = CreateDto::class;

    /**
     * Form data for the add form.
     *
     * Format is name => value
     * name => "route" means get value from route,
     * see conviction controller
     *
     * @var array
     */
    protected $defaultData = [
        'case' => 'route'
    ];

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteCommand = DeleteDto::class;
    protected $deleteModalTitle = 'internal.delete-action-trait.title';

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions']
    ];

    /**
     * Alter Form for add
     *
     * @param FormInterface           $form        form
     * @param array                   $initialData initialData
     *
     * @return ViewModel
     */
    public function alterFormForAdd($form, $initialData)
    {
        return $this->alterFormForCase($form, $initialData);
    }

    /**
     * Alter Form for edit
     *
     * @param FormInterface           $form        form
     * @param array                   $initialData initialData
     *
     * @return FormInterface
     */
    public function alterFormForEdit($form, $initialData)
    {
        return $this->alterFormForCase($form, $initialData);
    }

    /**
     * Alter Form based on Case details
     *
     * @param FormInterface           $form        form
     * @param array                   $initialData initialData
     *
     * @return FormInterface
     */
    private function alterFormForCase($form, $initialData)
    {
        $caseData = $this->getCaseData();

        $form->get('fields')->get('attachedTo')->setValueOptions(
            [
                'licence' => [
                    'label' => 'Licence',
                    'options' => [
                        RefData::ATTACHED_TO_LICENCE => 'Licence (' . $caseData['licence']['licNo'] . ')'
                    ]
                ],
                'OC' => [
                    'label' => 'OC Address',
                    'options' => $this->getOperatingCentreListOptions($caseData)
                ]
            ]
        );

        return $form;
    }

    /**
     * Returns the case data with attached licence, OC and address info
     *
     * @return mixed
     * @throws DataServiceException
     */
    private function getCaseData()
    {
        // get the case
        $params = ['id' => (int) $this->params()->fromRoute('case')];
        $query = CasesWithLicenceDto::create($params);

        $response = $this->handleQuery($query);

        if ($response->isOk()) {
            $caseData = $response->getResult();
        } else {
            throw new DataServiceException('Unable to load case data');
        }

        return $caseData;
    }

    /**
     * Extracts the addresses for each operating centre and formats them using the address view helper (not ideal)
     *
     * @param array $caseData caseData
     *
     * @return array
     */
    private function getOperatingCentreListOptions($caseData)
    {
        $optionList = [];
        if (isset($caseData['licence']['operatingCentres'])) {
            $addressViewHelper = $this->viewHelperManager->get('Address');
            foreach ($caseData['licence']['operatingCentres'] as $operatingCentreDetails) {
                $optionList[$operatingCentreDetails['operatingCentre']['id']] =
                    $addressViewHelper($operatingCentreDetails['operatingCentre']['address']);
            }
        }

        return $optionList;
    }
}
