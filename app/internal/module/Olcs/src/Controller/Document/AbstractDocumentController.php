<?php

/**
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Document;

use Common\Data\Mapper\LetterGenerationDocument;
use Common\RefData;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocument;
use Dvsa\Olcs\Transfer\Query\Application\Application;
use Dvsa\Olcs\Transfer\Query\Cases\Cases;
use Dvsa\Olcs\Transfer\Query\Document\Letter;
use Zend\View\Model\ViewModel;
use Olcs\Controller\AbstractController;
use Common\Category;

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
        'irfoOrganisation' => 'operator/documents',
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
        'irfoOrganisation' => Category::CATEGORY_IRFO,
    ];

    /**
     * How to map case types to category IDs
     */
    protected $caseCategoryMap = [
        RefData::CASE_TYPE_LICENCE => Category::CATEGORY_LICENSING,
        RefData::CASE_TYPE_APPLICATION => Category::CATEGORY_LICENSING,
        RefData::CASE_TYPE_TM  => Category::CATEGORY_TRANSPORT_MANAGER,
        RefData::CASE_TYPE_IMPOUNDING => Category::CATEGORY_LICENSING
    ];

    protected $docData = [];

    private $caseData;

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
            case 'irfoOrganisation':
                return 'organisation';

            default:
                return $type;
        }
    }

    protected function fetchDocData()
    {
        $response = $this->handleQuery(Letter::create(['id' => $this->params('doc')]));

        if ($response->isOk()) {
            return LetterGenerationDocument::mapFromResult($response->getResult());
        } else {
            return [];
        }
    }

    protected function removeDocument($id)
    {
        return $this->handleCommand(DeleteDocument::create(['id' => $id]));
    }

    protected function redirectToDocumentRoute($type, $action, $routeParams, $ajax = false)
    {
        $route = $this->documentRouteMap[$type];

        if (!is_null($action)) {
            if (!empty($routeParams['entityType']) && !empty($routeParams['entityId'])) {
                // if both the entityType and the entityId has some values then use the entity routing
                $route .= '/entity';
            }

            $route .= '/' . $action;
        }

        if ($ajax) {
            return $this->redirect()->toRouteAjax($route, $routeParams);
        }
        return $this->redirect()->toRoute($route, $routeParams);
    }

    protected function getLicenceIdForApplication()
    {
        $applicationId = $this->params()->fromRoute('application');

        $response = $this->handleQuery(Application::create(['id' => $applicationId]));

        return $response->getResult()['licence']['id'];
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
        if ($this->caseData === null) {
            $caseId = $this->params()->fromRoute('case');

            $response = $this->handleQuery(Cases::create(['id' => $caseId]));

            $this->caseData = $response->getResult();
        }

        return $this->caseData;
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
}
