<?php

/**
 * Case Penalty Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Cases\Penalty;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;
use Common\Service\Table\TableBuilder;
use Olcs\Form\Model\Form\Comment as CommentForm;
use Olcs\Data\Mapper\PenaltyCommentBox as CommentMapper;
use Dvsa\Olcs\Transfer\Query\Cases\Cases as CommentItemDto;
use Dvsa\Olcs\Transfer\Command\Cases\UpdatePenaltiesNote as CommentUpdateDto;
use Olcs\Data\Mapper\GenericFields;
use Dvsa\Olcs\Transfer\Command\Cases\Si\Applied\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Query\Cases\Si\Applied\Penalty as ItemDto;
use Dvsa\Olcs\Transfer\Query\Cases\Si\Si as SingleSiDto;
use Dvsa\Olcs\Transfer\Command\Cases\Si\Applied\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\Si\Applied\Update as UpdateDto;
use Olcs\Form\Model\Form\ErruPenalty;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;

/**
 * Case Penalty Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PenaltyController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_details_penalties';

    protected $commentFormClass = CommentForm::class;
    protected $commentItemDto = CommentItemDto::class;
    protected $commentItemParams = ['id' => 'case', 'case' => 'case'];
    protected $commentUpdateCommand = CommentUpdateDto::class;
    protected $commentMapperClass = CommentMapper::class;
    protected $commentTitle = 'Erru penalties';

    protected $createCommand = CreateDto::class; //add action creates applied penalties
    protected $updateCommand = UpdateDto::class; //edit action updates applied penalties

    protected $deleteCommand = DeleteDto::class; //delete action deletes applied penalties
    protected $deleteParams = ['id'];
    protected $deleteModalTitle = 'Delete applied penalty';

    protected $formClass = ErruPenalty::class;
    protected $mapperClass = GenericFields::class;
    protected $itemDto = ItemDto::class;

    protected $defaultData = [
        'case' => AddFormDefaultData::FROM_ROUTE,
        'si' => AddFormDefaultData::FROM_ROUTE,
        'id' => AddFormDefaultData::FROM_ROUTE
    ];

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = array(
        'indexAction' => ['table-actions']
    );

    /**
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/cases/partials/left');

        return $view;
    }

    /**
     * Loads the tables and read only data
     *
     * @return array|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $data = $this->getPenaltyData();

        $this->placeholder()->setPlaceholder('penalties', $data);
        $this->getErruTable('erru-imposed', 'imposedErrus', $data);
        $this->getErruTable('erru-requested', 'requestedErrus', $data);
        $this->getErruTable('erru-applied', 'appliedPenalties', $data);
        $this->getCommentBox();

        return $this->viewBuilder()->buildViewFromTemplate('sections/cases/pages/penalties');
    }

    /**
     * There is more than one table on the page so we can't use the usual method in abstractInternalController
     *
     * @param string $tableName
     * @param string $dataKey
     * @param array  $data      Penalty data
     */
    private function getErruTable($tableName, $dataKey, $data)
    {
        if (isset($data[$dataKey]) && !empty($data[$dataKey])) {
            $tableData = [
                'Count' => count($data[$dataKey]),
                'Results' => $data[$dataKey]
            ];
        } else {
            $tableData = [
                'Count' => 0,
                'Results' => []
            ];
        }

        //multiple tables on a page, so we need to give our plugin a new table builder each time
        $tableBuilder = new TableBuilder($this->getServiceLocator());
        $this->table()->setTableBuilder($tableBuilder);
        $this->placeholder()->setPlaceholder($tableName, $this->table()->buildTable($tableName, $tableData, []));
    }

    /**
     * Get Penalty data for the case
     *
     * @return array
     */
    private function getPenaltyData()
    {
        $response = $this->handleQuery(
            SingleSiDto::create(['id' => $this->params()->fromRoute('si')])
        );

        if (!$response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            return [];
        }

        return $response->getResult();
    }
}
