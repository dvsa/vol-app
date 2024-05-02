<?php

namespace Admin\Controller;

use DateTime;
use Dvsa\Olcs\Transfer\Command\Permits\QueueAcceptScoring;
use Dvsa\Olcs\Transfer\Command\Permits\QueueRunScoring;
use Dvsa\Olcs\Transfer\Query\Permits\PostScoringReport;
use Dvsa\Olcs\Transfer\Query\Permits\StockAlignmentReport;
use Dvsa\Olcs\Transfer\Query\Permits\StockOperationsPermitted;
use Laminas\Escaper\Escaper;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Mvc\Controller\ParameterProvider\ConfirmItem;

class IrhpPermitScoringController extends AbstractIrhpPermitAdminController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-permits';
    protected $tableViewTemplate = 'pages/irhp-permit-scoring/index';

    protected $redirectConfig = [
        'accept' => [
            'action' => 'index'
        ],
        'run' => [
            'action' => 'index'
        ]
    ];

    protected $inlineScripts = [
        'indexAction' => ['permits-scoring']
    ];

    /**
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-permits',
                'navigationTitle' => '',
                'stockId' => $this->params()->fromRoute()['stockId']
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * @return Response|ViewModel
     */
    public function indexAction()
    {
        $visibilityResult = $this->handleQuery(
            StockOperationsPermitted::create([ 'id' => $this->params()->fromRoute('stockId') ])
        );

        $this->placeholder()->setPlaceholder('operationsPermitted', $visibilityResult->getResult());
        $this->placeholder()->setPlaceholder('stockId', $this->params()->fromRoute('stockId'));

        return $this->viewBuilder()->buildViewFromTemplate($this->tableViewTemplate);
    }

    /**
     * @return array|mixed|ViewModel
     */
    public function acceptAction()
    {
        return parent::confirmCommand(
            new ConfirmItem(['id' => 'stockId']),
            QueueAcceptScoring::class,
            'Accept scoring - are you sure?',
            'This will Accept scoring. Are you sure?',
            'Accept handler successfully triggered'
        );
    }

    /**
     * @return array|mixed|ViewModel
     */
    public function runStandardAction()
    {
        return parent::confirmCommand(
            new ConfirmItem(['id' => 'stockId']),
            QueueRunScoring::class,
            'Run scoring - are you sure?',
            'This will run scoring with a computed mean deviation. Are you sure?',
            'Scoring successfully triggered'
        );
    }

    /**
     * @return array|mixed|ViewModel
     */
    public function runWithMeanDeviationAction()
    {
        $deviation = $this->params()->fromRoute()['deviation'];
        $escaper = new Escaper();

        return parent::confirmCommand(
            new ConfirmItem(
                [
                    'id' => 'stockId',
                    'deviation' => 'deviation',
                ]
            ),
            QueueRunScoring::class,
            'Run scoring - are you sure?',
            'This will run scoring with a mean deviation override of ' . $escaper->escapeHtml($deviation) . '. Are you sure?',
            'Scoring successfully triggered'
        );
    }

    /**
     * @return Response
     */
    public function alignStockAction()
    {
        $stockId = $this->params()->fromRoute('stockId');

        $response = $this->handleQuery(
            StockAlignmentReport::create(['id' => $stockId])
        );

        $result = $response->getResult();

        $commaSeparatedRows = [];
        foreach ($result['rows'] as $row) {
            $commaSeparatedRows[] = '"' . implode('","', $row) . '"';
        }

        $content = implode("\n", $commaSeparatedRows);
        $formattedDateTime = (new DateTime())->format('Ymd');
        $filename = sprintf('stock-alignment-report-stock%s-%s.csv', $stockId, $formattedDateTime);

        return $this->csvResponse($filename, $content);
    }

    /**
     * @return Response
     */
    public function postScoringReportAction()
    {
        $stockId = $this->params()->fromRoute('stockId');

        $response = $this->handleQuery(
            PostScoringReport::create([ 'id' => $stockId ])
        );

        $result = $response->getResult();

        $commaSeparatedRows = [];
        foreach ($result['rows'] as $row) {
            $commaSeparatedRows[] = implode('', $row);
        }

        $content = implode("\n", $commaSeparatedRows);
        $formattedDateTime = (new DateTime())->format('Ymd');
        $filename = sprintf('post-scoring-report-stock%s-%s.csv', $stockId, $formattedDateTime);

        return $this->csvResponse($filename, $content);
    }

    /**
     * @return Response
     */
    private function csvResponse($filename, $content)
    {
        $response = new Response();
        $response->setStatusCode(Response::STATUS_CODE_200);
        $response->getHeaders()->addHeaders(
            [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($content)
            ]
        );
        $response->setContent($content);

        return $response;
    }

    /**
     * @return JsonModel
     */
    public function statusAction()
    {
        $stockOperationsPermitted = $this->handleQuery(
            StockOperationsPermitted::create([ 'id' => $this->params()->fromRoute('stockId') ])
        );

        return new JsonModel($stockOperationsPermitted->getResult());
    }
}
