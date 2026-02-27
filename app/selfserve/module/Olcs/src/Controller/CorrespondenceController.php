<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Correspondence\AccessCorrespondence;
use Dvsa\Olcs\Transfer\Query\Correspondence\Correspondence;
use Dvsa\Olcs\Transfer\Query\Correspondence\Correspondences;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Class CorrespondenceController
 *
 * List an operator's correspondence.
 *
 * @package Olcs\Controller
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class CorrespondenceController extends AbstractController
{
    use Lva\Traits\ExternalControllerTrait;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected TableFactory $tableFactory
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Display the table and all the correspondence for the given organisation.
     *
     * @return ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        $params = [
            'page' => $this->params()->fromQuery('page', 1),
            'limit' => $this->params()->fromQuery('limit', 10),
            'sort' => $this->params()->fromQuery('sort', 'd.issuedDate'),
            'order' => $this->params()->fromQuery('order', 'DESC'),
            'organisation' => $this->getCurrentOrganisationId(),
            'query' => $this->params()->fromQuery(),
        ];

        $response = $this->handleQuery(Correspondences::create($params));
        if ($response === null) {
            return $this->notFoundAction();
        }

        if ($response->isOk()) {
            $docs = $response->getResult();
        } else {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
            $docs = [];
        }

        $table = $this->tableFactory
            ->buildTable('correspondence', $this->formatTableData($docs), $params);

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('correspondence');

        return $view;
    }

    /**
     * A gateway method for accessing a document with method sets the accessed
     * parameter on the correspondence record.
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    public function accessCorrespondenceAction()
    {
        $correspondence = $this->params()->fromRoute('correspondenceId', null);
        $command = AccessCorrespondence::create(
            [
                'id' => $correspondence
            ]
        );

        $response = $this->handleCommand($command);
        if (!$response->isOk()) {
            if ($response->isClientError() || $response->isServerError()) {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }

            return $this->notFoundAction();
        }

        $query = Correspondence::create(
            [
                'id' => $correspondence
            ]
        );

        $response = $this->handleQuery($query);
        if (!$response->isOk()) {
            if ($response->isClientError() || $response->isServerError()) {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }

            return $this->notFoundAction();
        }

        $correspondence = $response->getResult();

        return $this->redirect()->toRoute(
            'getfile',
            [
                'identifier' => $correspondence['document']['id']
            ]
        );
    }

    /**
     * Format the correspondence data for displaying within the table.
     *
     * @param array $docs Corresposdence data
     *
     * @return array
     */
    protected function formatTableData(array $docs = [])
    {
        $docs['results'] = array_map(
            fn($correspondence) => [
                'id' => $correspondence['id'],
                'correspondence' => $correspondence,
                'licence' => $correspondence['licence'],
                'date' => $correspondence['createdOn'],
            ],
            $docs['results']
        );

        return $docs;
    }
}
