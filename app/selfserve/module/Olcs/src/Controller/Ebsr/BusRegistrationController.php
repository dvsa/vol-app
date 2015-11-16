<?php

namespace Olcs\Controller\Ebsr;

use Common\Controller\AbstractActionController;
use Common\Exception\ResourceNotFoundException;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\TxcInboxList as ListDto;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\TxcInboxByBusReg as ItemDto;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\UpdateTxcInbox as UpdateTxcInboxDto;
use Dvsa\Olcs\Transfer\Query\Bus\RegistrationHistoryList as BusRegVariationHistoryDto;
use Common\Controller\Lva\AbstractController;
use Zend\View\Model\ViewModel;

/**
 * Class BusRegistrationController
 */
class BusRegistrationController extends AbstractController
{
    /**
     * Lists all EBSR's with filter search form
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $request = $this->getRequest();

            $postData = $request->getPost();

            if (isset($postData['action']) && isset($postData['table']) && $postData['table'] == 'txc-inbox') {
                return $this->processMarkAsRead($postData);
            }
            return $this->processSearch($postData);
        }

        $params = [];
        $params['ebsrSubmissionType'] = $this->params()->fromQuery('subType');
        $params['ebsrSubmissionStatus'] = $this->params()->fromQuery('status');
        $params['sort'] = $this->params()->fromQuery('sort', 'createdOn');
        $params['order'] = $this->params()->fromQuery('order', 'DESC');
        $params['page'] = $this->params()->fromQuery('page', 1);
        $params['limit'] = $this->params()->fromQuery('limit', 25);
        $params['query'] = $this->params()->fromQuery();

        $query = ListDto::create($params);

        $response = $this->handleQuery($query);

        // handle response
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addCurrentUnknownError();
        }

        $busRegistrationTable = '';
        if ($response->isOk()) {
            $result = $response->getResult();

            /** @var \Common\Service\Table\TableBuilder $tableBuilder */
            $tableBuilder = $this->getServiceLocator()->get('Table');

            $busRegistrationTable = $tableBuilder->buildTable(
                'txc-inbox',
                ['Results' => $result['results'], 'Count' => $result['count']],
                $params,
                false
            );

            // set disabled so non-LA's dont get a pointless 'mark as read' button
            $userData = $this->currentUser()->getUserData();
            if (empty($userData['localAuthorityId'])) {
                $busRegistrationTable->setDisabled(true);
            }
        }

        $filterForm = $this->getFilterForm($params);

        // setup layout and view
        $layout = $this->generateLayout(
            [
                'pageTitle' => 'bus-registrations-index-title',
                'pageHeaderText'=> 'bus-registrations-index-subtitle',
                'searchForm' => $filterForm,
                'pageHeaderUrl' => [
                    'route' => 'bus-registration/ebsr',
                    'params' => [
                        'action' => 'upload'
                    ],
                    'text' => 'register-cancel-update-service'
                ]
            ]
        );

        $content = $this->generateContent(
            'olcs/bus-registration/index',
            [
                'busRegistrationTable' => $busRegistrationTable,
            ]
        );

        $layout->addChild($content, 'content');

        return $layout;
    }

    /**
     * Process those marked in table as read
     *
     * @param $data
     * @return array
     */
    public function processMarkAsRead($data)
    {
        $command = UpdateTxcInboxDto::create(
            [
                'ids' => $data['id'],
            ]
        );

        $response = $this->handleCommand($command);

        // handle response
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {

            $params['subType'] = $this->params()->fromQuery('subType');
            $params['status'] = $this->params()->fromQuery('status');

            return $this->redirect()->toRoute(null, $params, [], false);
        }
    }

    /**
     * Process the search, simply sets up the GET params and redirects
     *
     * @param $data
     */
    private function processSearch($data)
    {
        $params = $this->params()->fromQuery();

        $params['subType'] = empty($data['fields']['subType']) ? null : $data['fields']['subType'];

        $params['status'] = empty($data['fields']['status']) ? null : $data['fields']['status'];

        return $this->redirect()->toRoute(null, [], ['query' => $params], true);
    }

    /**
     * Bus registration details page
     *
     * @return array|ViewModel
     * @throws ResourceNotFoundException
     */
    public function detailsAction()
    {
        $id = $this->params()->fromRoute('busRegId');

        $query = ItemDto::create(['busReg' => $id]);

        $response = $this->handleQuery($query);

        // handle response
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $results = $response->getResult();
        }

        $documents = [];

        if ($this->isGranted('selfserve-ebsr-documents')) {
            if (!empty($results['pdfDocument'])) {
                $documents[] = $results['pdfDocument'];
            }
            if (!empty($results['routeDocument'])) {
                $documents[] = $results['routeDocument'];
            }
            if (!empty($results['zipDocument'])) {
                $documents[] = $results['zipDocument'];
            }
        }

        // setup layout and view
        $content = $this->generateContent(
            'olcs/bus-registration/details',
            [
                'registrationDetails' => $results['busReg'],
                'documents' => $documents,
                'variationHistoryTable' => $this->fetchVariationHistoryTable($results['busReg']['id'])
            ]
        );

        return $content;
    }

    /**
     * Method to generate the Variation History table
     *
     * @param $busRegId
     * @return array|string
     */
    private function fetchVariationHistoryTable($busRegId)
    {
        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->getServiceLocator()->get('Table');

        $query = BusRegVariationHistoryDto::create(
            [
                'id' => $busRegId,
                'sort' => 'variationNo',
                'order' => 'DESC'
            ]
        );

        $response = $this->handleQuery($query);

        // handle response
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $result = $response->getResult();
            return $tableBuilder->buildTable(
                'bus-reg-variation-history',
                $result,
                ['url' => $this->plugin('url')],
                false
            );
        }

        return null;
    }

    /**
     * Set up the layout with title, subtitle and content
     *
     * @param null $title
     * @param null $subtitle
     * @return \Zend\View\Model\ViewModel
     */
    private function generateLayout($data = [])
    {
        $layout = new \Zend\View\Model\ViewModel(
            $data
        );

        $layout->setTemplate('layouts/search');

        return $layout;
    }

    /**
     * Generate page content
     *
     * @param $template
     * @param array $data
     * @return ViewModel
     */
    private function generateContent($template, $data = [])
    {
        $content = new ViewModel($data);

        $content->setTemplate($template);
        return $content;
    }

    /**
     * Get and setup the filter form
     *
     * @param $params
     * @return mixed
     */
    public function getFilterForm($params)
    {
        $filterForm = $this->getServiceLocator()->get('Helper\Form')->createForm('BusRegFilterForm');

        $filterForm->setData(
            [
                'fields' => [
                    'subType' => $params['ebsrSubmissionType'],
                    'status' => $params['ebsrSubmissionStatus']
                ]
            ]
        );

        return $filterForm;
    }
}
