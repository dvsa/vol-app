<?php

/**
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;
use Olcs\Controller\AbstractController;

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
