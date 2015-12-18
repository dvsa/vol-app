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
use Dvsa\Olcs\Transfer\Command\Cases\Si\SendResponse as SendResponseCmd;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;

/**
 * Case Penalty Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PenaltyController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    protected $commentFormClass = CommentForm::class;
    protected $commentItemDto = CommentItemDto::class;
    protected $commentItemParams = ['id' => 'case', 'case' => 'case'];
    protected $commentUpdateCommand = CommentUpdateDto::class;
    protected $commentMapperClass = CommentMapper::class;
    protected $commentTitle = 'Erru Penalties';

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/cases/partials/left');

        return $view;
    }

    /**
     * Sends the response back to Erru
     */
    public function sendAction()
    {
        return $this->processCommand(new GenericItem(['case' => 'case']), SendResponseCmd::class);
    }

    /**
     * Loads the tables and read only data
     *
     * @return array|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $data = $this->getPenaltyData();

        if (isset($data['results'][0])) {
            $this->placeholder()->setPlaceholder('penalties', $data['results'][0]);
            $this->getErruTable('erru-imposed', 'imposedErrus', $data);
            $this->getErruTable('erru-requested', 'requestedErrus', $data);
            $this->getErruTable('erru-applied', 'appliedPenalties', $data);
            $this->getCommentBox();
        }

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
        if (isset($data['results'][0][$dataKey]) && !empty($data['results'][0][$dataKey])) {
            $tableData = [
                'Count' => count($data['results'][0][$dataKey]),
                'Results' => $data['results'][0][$dataKey]
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
            \Dvsa\Olcs\Transfer\Query\Cases\Si\GetList::create(
                ['case' => $this->params()->fromRoute('case')]
            )
        );

        if (!$response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            return [];
        }

        return $response->getResult();
    }
}
