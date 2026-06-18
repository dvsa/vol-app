<?php

namespace Common\Controller\Lva\Adapters;

use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Data\CategoryDataService as Category;
use Dvsa\Olcs\Transfer\Query\Application\FinancialEvidence;
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
    public function getDocuments($applicationId)
    {
        $documents = $this->getData($applicationId)['documents'];

        return is_array($documents) ? $documents : [];
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
}
