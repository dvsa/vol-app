<?php

/**
 * Case Penalty Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller\Cases\Penalty;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;

/**
 * Case Penalty Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PenaltyController extends OlcsController\CrudAbstract implements CaseControllerInterface, LeftViewProvider
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier
     *
     *
     * @var string
     */
    protected $identifier = 'case';

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'case';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = '';

    /**
     * Name of comment box field.
     *
     * @var string
     */
    protected $commentBoxName = 'penaltiesNote';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'SeriousInfringement';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'ErruPenalty';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_details_penalties';

    /**
     * @var array
     */
    protected $inlineScripts = ['table-actions'];

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/cases/partials/left');

        return $view;
    }

    /**
     * Simple redirect to index.
     */
    public function redirectToIndex()
    {
        return $this->redirectToRouteAjax(
            null,
            ['action'=>'index', 'case' => $this->params()->fromRoute('case')],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

    /**
     * Sends the response back to Erru
     */
    public function sendAction()
    {
        $response = $this->handleCommand(
            \Dvsa\Olcs\Transfer\Command\Cases\Si\SendResponse::create(
                ['case' => $this->params()->fromRoute('case')]
            )
        );

        if ($response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('Response sent successfully');
        } else {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addUnknownError();
        }

        return $this->redirectToIndex();
    }

    /**
     * Loads the tables and read only data
     *
     * @return array|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $data = $this->getPenaltyData();

        //if a table crud button has been clicked then
        //we need to intercept the post and redirect to AppliedPenaltyController
        $postedVars = $this->params()->fromPost();

        if (isset($postedVars['action'])) {
            return $this->redirectToRoute(
                'case_penalty_edit',
                [
                    'action' => $postedVars['action'],
                    'seriousInfringement' => $data['results'][0]['id'],
                    'id' => isset($postedVars['id']) ? $postedVars['id'] : null
                ],
                ['code' => '303'], // Why? No cache is set with a 303 :)
                true
            );
        }

        $view = $this->getView([]);

        $this->buildCommentsBoxIntoView();

        if (isset($data['results'][0])) {
            $this->getViewHelperManager()->get('placeholder')->getContainer('penalties')->set($data['results'][0]);
            $this->getErruTable('erru-imposed', 'imposedErrus', $data);
            $this->getErruTable('erru-requested', 'requestedErrus', $data);
            $this->getErruTable('erru-applied', 'appliedPenalties', $data);
        }

        $view->setTemplate('sections/cases/pages/penalties');

        return $this->renderView($view);
    }

    /**
     * There is more than one table on the page so we can't use crud abstract
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

        $this->getViewHelperManager()->get('placeholder')->getContainer($tableName)->set(
            $this->getTable($tableName, $tableData, [])
        );
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
