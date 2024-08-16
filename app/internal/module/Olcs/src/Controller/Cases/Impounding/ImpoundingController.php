<?php

namespace Olcs\Controller\Cases\Impounding;

use Common\RefData as RefData;
use Dvsa\Olcs\Transfer\Command\Cases\Impounding\CreateImpounding as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\Impounding\DeleteImpounding as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Cases\Impounding\UpdateImpounding as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\Impounding\Impounding as ItemDto;
use Dvsa\Olcs\Transfer\Query\Cases\Impounding\ImpoundingList as ListDto;
use Laminas\Form\FormInterface;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Form\Model\Form\Impounding;

class ImpoundingController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    use ControllerTraits\GenerateActionTrait;

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_details_impounding';

    protected $routeIdentifier = 'impounding';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'impounding';
    protected $listDto = ListDto::class;
    protected $listVars = ['case'];

    /**
     * get method for View Model
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
    protected $itemDto = ItemDto::class;
    // 'id' => 'impounding', to => from
    protected $itemParams = ['case', 'id' => 'impounding'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Impounding::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = \Olcs\Data\Mapper\Impounding::class;
    protected $addContentTitle = 'Add impounding';
    protected $editContentTitle = 'Edit impounding';

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
    protected $deleteParams = ['id' => 'impounding'];
    protected $deleteModalTitle = 'Delete Impounding';

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = [
        'addAction' => ['forms/impounding'],
        'editAction' => ['forms/impounding'],
        'deleteAction' => ['forms/impounding'],
        'indexAction' => ['table-actions']
    ];

    /**
     * Defines additional allowed POST actions
     *
     * Format is action => config array
     *
     * @var array
     */
    protected $crudConfig = [
        'generate' => ['requireRows' => true],
    ];

    /**
     * Alter form for TM cases, set pubType and trafficAreas to be visible for publishing
     *
     * @param FormInterface $form        form
     * @param array                   $initialData initialData
     *
     * @return FormInterface
     */
    public function alterFormForEdit($form, $initialData)
    {
        // remove publish button if impounding type is NOT 'hearing'
        if ($initialData['fields']['impoundingType'] !== RefData::IMPOUNDING_TYPE_HEARING) {
            $form->get('form-actions')->remove('publish');
        } else {
            // set the label to republish if *any* publication has NOT been printed
            if (!empty($initialData['impounding']['publicationLinks'])) {
                foreach ($initialData['impounding']['publicationLinks'] as $pl) {
                    if (isset($pl['publication']) && $pl['publication']['pubStatus']['id'] !== 'pub_s_printed') {
                        $form->get('form-actions')->get('publish')->setLabel('Republish');
                        break;
                    }
                }
            }
        }

        return $form;
    }

    /**
     * Route for document generate action redirects
     *
     * @see Olcs\Controller\Traits\GenerateActionTrait
     *
     * @return string
     */
    protected function getDocumentGenerateRoute()
    {
        return 'case_licence_docs_attachments/entity/generate';
    }

    /**
     * Route params for document generate action redirects
     *
     * @see Olcs\Controller\Traits\GenerateActionTrait
     *
     * @return array
     */
    protected function getDocumentGenerateRouteParams()
    {
        return [
            'case' => $this->params()->fromRoute('case'),
            'entityType' => 'impounding',
            'entityId' => $this->params()->fromRoute('impounding')
        ];
    }
}
