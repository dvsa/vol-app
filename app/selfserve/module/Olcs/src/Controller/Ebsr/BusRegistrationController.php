<?php

namespace Olcs\Controller\Ebsr;

use Common\Controller\AbstractActionController;
use Common\Exception\ResourceNotFoundException;
<<<<<<< HEAD
use Dvsa\Olcs\Transfer\Query\Ebsr\SubmissionList as SubmissionListQuery;
=======
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\TxcInboxList as ListDto;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegWithDocuments as ItemDto;
use Dvsa\Olcs\Transfer\Query\Bus\RegistrationHistoryList as BusRegVariationHistoryDto;
>>>>>>> Replaced EbsrSubmissionList with TxcInbox

/**
 * Class BusRegVariationController
 */
class BusRegistrationController extends AbstractActionController
{

    protected $txcBundle = [
        'children' => [
            'pdfDocument' => [
                'criteria' => [
                    'subCategory' => 108,
                ]
            ],
            'routeDocument',
            'zipDocument',
            'busReg'
        ]
    ];

    /**
     * Lists all EBSR's with filter search form
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->getServiceLocator()->get('Table');

        $params = [];
        $params['ebsrSubmissionType'] = $this->params()->fromRoute('subType');
        $params['ebsrSubmissionStatus'] = $this->params()->fromRoute('status');
        $params['sort'] = $this->params()->fromRoute('sort');
        $params['order'] = $this->params()->fromRoute('order');
        $params['page'] = $this->params()->fromRoute('page');
        $params['limit'] = $this->params()->fromRoute('limit');
        $params['url'] = $this->plugin('url');

        $filterForm = $this->generateFormWithData(
            'BusRegFilterForm',
            'processSearch',
            [
                'fields' => [
                    'subType' => $params['ebsrSubmissionType'],
                    'status' => $params['ebsrSubmissionStatus']
                ]
            ]
        );

        $response = $this->handleQuery(SubmissionListQuery::create($params));

        $busRegistrationList = $response->getResult();

        $busRegistrationTable = $tableBuilder->buildTable(
            'bus-registrations',
            ['Results' => $busRegistrationList['result'], 'Count' => $busRegistrationList['count']],
            $params,
            false
        );

        $content = $this->getView(
            [
                'busRegistrationTable' => $busRegistrationTable,
            ]
        );
        $content->setTemplate('olcs/bus-registration/index');

        $layout = $this->getView(
            [
                'pageTitle' => 'bus-registrations-index-title',
                'pageHeaderText'=> 'bus-registrations-index-subtitle',
                'searchForm' => $filterForm,
                'pageHeaderUrl' => [
                    'route' => 'ebsr',
                    'params' => [
                        'action' => 'upload'
                    ],
                    'text' => 'register-cancel-update-service'
                ]
            ]
        );
        $layout->setTemplate('layouts/search');
        $layout->addChild($content, 'content');

        return $layout;
    }

    /**
     * Process the search, simply sets up the GET params and redirects
     * @param $data
     */
    protected function processSearch($data)
    {
        $params = [];
        if (!empty($data['fields']['subType'])) {
            $params['subType'] = $data['fields']['subType'];
        }
        if (!empty($data['fields']['status'])) {
            $params['status'] = $data['fields']['status'];
        }
        $this->setCaughtResponse(
            $this->redirectToRoute(
                null,
                $params,
                [],
                false
            )
        );
    }

    /**
     * Bus registration details page
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function detailsAction()
    {
        $id = $this->params()->fromRoute('busRegId');

        $busRegDataService = $this->getBusRegDataService();

        $registrationDetails = $busRegDataService->fetchDetail($id);

        if (empty($registrationDetails)) {
            throw new ResourceNotFoundException('Bus registration could not be found');
        }

        // call method to check permission to view docs
        $registrationDetails['documents'] = $this->getDocuments($registrationDetails);
        $latestPublication = $this->getLatestPublicationByType(
            $registrationDetails['licence'],
            'N&P'
        );

        $registrationDetails['npRreferenceNo'] = $latestPublication['publicationNo'];

        return $this->getView(
            [
                'registrationDetails' => $registrationDetails,
                'variationHistoryTable' => $this->fetchVariationHistoryTable($registrationDetails)
            ]
        );
    }

    /**
     * Method to generate the Variation History table
     *
     * @param $registrationDetails
     * @return string
     */
    private function fetchVariationHistoryTable($registrationDetails)
    {
        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->getServiceLocator()->get('Table');

        $busRegDataService = $this->getBusRegDataService();

        $variationHistory = $busRegDataService->fetchVariationHistory($registrationDetails['regNo']);

        $table = $tableBuilder->buildTable(
            'bus-reg-variation-history',
            $variationHistory,
            ['url' => $this->plugin('url')],
            false
        );

        return $table;
    }

    /**
     * Function to get documents from registrationDetails data
     * Based on permission 'selfserve-ebsr-documents' being granted
     *
     * @param $registrationDetails
     * @return null
     */
    private function getDocuments($registrationDetails)
    {
        $documents = [];

        $authService = $this->getServiceLocator()->get('ZfcRbac\Service\AuthorizationService');
        if ($authService->isGranted('selfserve-ebsr-documents')) {

            $userDetails = $this->getUserDetails();

            $txcInboxEntityService = $this->getTxcInboxEntityService();
            $documents =  $txcInboxEntityService->fetchBusRegDocuments(
                $registrationDetails['id'],
                $userDetails['localAuthority']
            );

        }
        return $documents;
    }

    /**
     * Returns the current logged in user details array
     * @return array
     */
    private function getUserDetails()
    {
        return $this->getServiceLocator()->get('Entity\User')->getUserDetails(
            $this->getLoggedInUser()
        );
    }

    /**
     * Returns the latest publication by type from a licence
     *
     * @param $licence
     * @param $type string
     * @return array|null
     */
    private function getLatestPublicationByType($licence, $type)
    {
        if (isset($licence['publicationLinks'][0]['publication'])) {
            usort(
                $licence['publicationLinks'],
                function ($a, $b) {
                    return strtotime($b['publication']['pubDate']) - strtotime($a['publication']['pubDate']);
                }
            );
            foreach ($licence['publicationLinks'] as $publicationLink) {
                if ($publicationLink['publication']['pubType'] == $type) {
                    return $publicationLink['publication'];
                }
            }
        }
        return null;
    }

    /**
     * @return \Common\Service\Entity\TxcInboxEntityService
     */
    public function getTxcInboxEntityService()
    {
        $entityService = $this->getServiceLocator()
            ->get('Entity\TxcInbox');

        return $entityService;
    }

    /**
     * @return \Common\Service\Data\BusReg
     */
    public function getBusRegDataService()
    {
        /** @var \Generic\Service\Data\BusReg $dataService */
        $dataService = $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Common\Service\Data\BusReg');
        return $dataService;
    }
}
