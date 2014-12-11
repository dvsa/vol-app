<?php

/**
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;
use Olcs\Controller\AbstractController;
use Common\Service\Data\CategoryDataService as Category;

/**
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractDocumentController extends AbstractController
{
    /**
     * For redirects
     */
    protected $documentRouteMap = [
        'licence'     => 'licence/documents',
        'application' => 'lva-application/documents',
        'case'        => 'case_licence_docs_attachments',
        'busReg'      => 'licence/bus-docs',
    ];

    /**
     * How to map route param types to category IDs (see category db table)
     */
    protected $categoryMap = [
        'licence'     => Category::CATEGORY_LICENSING,
        'application' => Category::CATEGORY_LICENSING,
        'case'        => Category::CATEGORY_LICENSING, // use Licensing for now
        'busReg'      => Category::CATEGORY_BUS_REGISTRATION,
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
            case 'licence':
            case 'application':
            case 'case':
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
        if (!is_null($action)) {
            $route = $this->documentRouteMap[$type].'/'.$action;
        } else {
            $route = $this->documentRouteMap[$type];
        }
        return $this->redirect()->toRoute($route, $routeParams);
    }

    protected function getLicenceIdForApplication()
    {
        $applicationId = $this->params()->fromRoute('application');
        return $this->getServiceLocator()->get('Entity\Application')
            ->getLicenceIdForApplication($applicationId);
    }

    protected function getLicenceIdForCase()
    {
        $caseId = $this->params()->fromRoute('case');
        $service = $this->getServiceLocator()->get('DataServiceManager')->get('Olcs\Service\Data\Cases');
        $case = $service->fetchCaseData($caseId);
        return isset($case['licence']['id']) ? $case['licence']['id'] : null;
    }
}
