<?php

/**
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;
use Olcs\Controller\AbstractController;
use Common\Service\Data\CategoryDataService as Category;

/**
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractDocumentController extends AbstractController
{
    // AC specifies this timestamp format...
    const DOCUMENT_TIMESTAMP_FORMAT = 'YmdHi';

    /**
     * For redirects
     */
    protected $documentRouteMap = [
        'licence'          => 'licence/documents',
        'application'      => 'lva-application/documents',
        'case'             => 'case_licence_docs_attachments',
        'busReg'           => 'licence/bus-docs',
        'transportManager' => 'transport-manager/documents',
    ];

    /**
     * How to map route param types to category IDs (see category db table)
     */
    protected $categoryMap = [
        'licence'          => Category::CATEGORY_LICENSING,
        'busReg'           => Category::CATEGORY_BUS_REGISTRATION,
        'case'             => null, // complex, depends on caseType
        'application'      => Category::CATEGORY_LICENSING, // Application isn't a document category
        'transportManager' => Category::CATEGORY_TRANSPORT_MANAGER,
        'statement'        => Category::CATEGORY_COMPLIANCE,
        'hearing'          => Category::CATEGORY_COMPLIANCE,
        'opposition'       => Category::CATEGORY_ENVIRONMENTAL,
        'complaint'        => Category::CATEGORY_LICENSING,
    ];

    /**
     * How to map case types to category IDs
     */
    protected $caseCategoryMap = [
        'case_t_lic' => Category::CATEGORY_LICENSING,
        'case_t_app' => Category::CATEGORY_LICENSING,
        'case_t_tm'  => Category::CATEGORY_TRANSPORT_MANAGER,
        'case_t_imp' => Category::CATEGORY_LICENSING
    ];

    /**
     * Where to store any temporarily generated documents
     */
    const TMP_STORAGE_PATH = 'tmp';

    /**
     * the keyspace where we store our extra metadata about
     * each document in jackrabbit
     */
    const METADATA_KEY = 'data';

    protected $tmpData = [];

    /**
     * Maps an entity type to the key needed to get the id from the route
     *
     * Note: busReg routing is set up completely differently to everything else :(
     *
     * @param string $type
     * @return string
     */
    public function getRouteParamKeyForType($type)
    {
        switch ($type) {
            case 'busReg':
                return 'busRegId';

            default:
                return $type;
        }
    }

    protected function getContentStore()
    {
        return $this->getServiceLocator()->get('ContentStore');
    }

    protected function getDocumentService()
    {
        return $this->getServiceLocator()->get('Document');
    }

    protected function getTmpPath()
    {
        return self::TMP_STORAGE_PATH . '/' . $this->params('tmpId');
    }

    protected function removeTmpData()
    {
        $this->getUploader()->remove(
            $this->params('tmpId'),
            self::TMP_STORAGE_PATH
        );
    }

    protected function fetchTmpData()
    {
        if (empty($this->tmpData)) {
            $path = $this->getTmpPath();
            $meta = $this->getContentStore()
                ->readMeta($path);

            if ($meta['exists'] === true) {
                $key = 'meta:' . self::METADATA_KEY;

                $this->tmpData = json_decode(
                    $meta['metadata'][$key],
                    true
                );
            }
        }
        return $this->tmpData;
    }

    protected function formatFilename($input)
    {
        return str_replace([' ', '/'], '_', $input);
    }

    protected function redirectToDocumentRoute($type, $action, $routeParams)
    {
        $route = $this->documentRouteMap[$type];

        if (!empty($routeParams['entityType']) && !empty($routeParams['entityId'])) {
            // if both the entityType and the entityId has some values then use the entity routing
            $route .= '/entity';
        }

        if (!is_null($action)) {
            $route .= '/'.$action;
        }

        return $this->redirect()->toRoute($route, $routeParams);
    }

    protected function getLicenceIdForApplication()
    {
        $applicationId = $this->params()->fromRoute('application');
        return $this->getServiceLocator()->get('Entity\Application')
            ->getLicenceIdForApplication($applicationId);
    }

    protected function getCategoryForType($type)
    {
        if ($type !== 'case') {
            return $this->categoryMap[$type];
        }
        $case = $this->getCase();
        return $this->caseCategoryMap[$case['caseType']['id']];
    }

    protected function getCase()
    {
        $caseId = $this->params()->fromRoute('case');
        $service = $this->getServiceLocator()->get('DataServiceManager')->get('Olcs\Service\Data\Cases');
        return $service->fetchCaseData($caseId);
    }

    protected function getCaseData()
    {
        $case = $this->getCase();
        $data = [
            'case' => $case['id']
        ];
        switch ($case['caseType']['id']) {
            case 'case_t_tm':
                $data['transportManager'] = $case['transportManager']['id'];
                break;

            default:
                $data['licence'] = $case['licence']['id'];
                break;
        }

        return $data;
    }

    public function getDocumentTimestamp()
    {
        return $this->getServiceLocator()
            ->get('Helper\Date')
            ->getDate(self::DOCUMENT_TIMESTAMP_FORMAT);
    }
}
