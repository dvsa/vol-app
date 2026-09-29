<?php

namespace Common\Controller\Lva\Adapters;

use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Data\CategoryDataService as Category;
use Dvsa\Olcs\Transfer\Query\Application\FinancialEvidence;
use Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Psr\Container\ContainerInterface;

/**
 * Application Financial Evidence Adapter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationFinancialEvidenceAdapter extends AbstractFinancialEvidenceAdapter
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    protected $applicationData; // cache
    /**
     * @param Common\Form\Form
     */
    #[\Override]
    public function alterFormForLva($form): void
    {
        $form->get('finance')->get('requiredFinance')
            ->setValue('markup-required-finance-application');
    }

    /**
     * @param int $applicationId
     * @return array
     */
    #[\Override]
    public function getDocuments($applicationId, $showAnalysisStatus = false)
    {
        $documents = $this->getData($applicationId)['documents'];
        $documents = is_array($documents) ? $documents : [];

        if ($showAnalysisStatus) {
            $analysesByDocumentId = $this->getAnalysesByDocumentId($applicationId);

            foreach ($documents as &$document) {
                $analysis = $analysesByDocumentId[$document['id']] ?? null;
                $document['analysisStatus'] = $analysis['status'] ?? null;
            }
            unset($document);
        }

        return $documents;
    }

    /**
     * @param array $file
     * @param int $applicationId
     * @return array
     */
    #[\Override]
    public function getUploadMetaData($file, $applicationId)
    {
        $licenceId = $this->getData($applicationId)['licence']['id'];

        return [
            'application' => $applicationId,
            'description' => $file['name'],
            'category'    => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL,
            'licence'     => $licenceId,
        ];
    }

    /**
     * Single call to get all the application data from the backend, including
     * financial evidence data and documents.
     */
    #[\Override]
    public function getData($applicationId, $noCache = false)
    {
        if (is_null($this->applicationData) || $noCache) {
            $query = $this->container->get(AnnotationBuilder::class)
                ->createQuery(FinancialEvidence::create(['id' => $applicationId]));

            $response = $this->container->get(CachingQueryService::class)->send($query);

            $this->applicationData = $response->getResult();
        }

        return $this->applicationData;
    }

    /**
     * One page of analyses is fetched at the largest limit the transfer validation allows; the
     * newest analysis per document wins below, so the list is ordered newest first.
     */
    private const int ANALYSIS_PAGE_LIMIT = 100;

    protected function getAnalysesByDocumentId(int $applicationId): array
    {
        $query = $this->container->get(AnnotationBuilder::class)
            ->createQuery(DocumentAnalysisList::create([
                'application' => $applicationId,
                'page' => 1,
                'limit' => self::ANALYSIS_PAGE_LIMIT,
                'sort' => 'createdOn',
                'order' => 'DESC',
            ]));

        $response = $this->container->get(CachingQueryService::class)->send($query);

        if (!$response->isOk()) {
            return [];
        }

        $indexed = [];
        foreach ($response->getResult()['analyses'] ?? [] as $analysis) {
            $indexed[$analysis['documentId']] ??= $analysis;
        }

        return $indexed;
    }
}
