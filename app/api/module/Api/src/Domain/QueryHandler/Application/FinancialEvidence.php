<?php

/**
 * Financial Evidence
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Financial Evidence
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialEvidence extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    /**
     * @var \Dvsa\Olcs\Api\Service\FinancialStandingHelperService $helper
     */
    protected $helper;

    public function handleQuery(QueryInterface $query)
    {
        $applicationRepo = $this->getRepo();
        $application = $applicationRepo->fetchUsingId($query, Query::HYDRATE_OBJECT);

        // add documents if not READ-ONLY user
        $financialDocuments = null;
        if (!$this->isReadOnlyInternalUser()) {
            $financialDocuments = $application->getApplicationDocuments(
                $applicationRepo->getCategoryReference(Category::CATEGORY_APPLICATION),
                $applicationRepo->getSubCategoryReference(SubCategory::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL)
            );
            $financialDocuments = $this->resultList($financialDocuments);
        }

        // add calculated finance data
        $financialEvidence = $this->getTotalNumberOfAuthorisedVehicles($application);
        $financialEvidence['requiredFinance'] = $this->helper->getRequiredFinance($application);
        $financialEvidence = array_merge(
            $financialEvidence,
            $this->helper->getRatesForView()
        );

        return $this->result(
            $application,
            [
                'licence',
            ],
            [
                'documents' => $financialDocuments,
                'financialEvidence' => $financialEvidence
            ]
        );
    }

    /**
     * Get the total vehicle authority which includes:
     *
     *  The vehicles on this application
     *  PLUS the vehicles across other licence records with a status of:
     *   Valid
     *   Suspended
     *   Curtailed
     *  PLUS the vehicles across other new applications with a status of:
     *   Under consideration
     *   Granted
     *
     * @param ApplicationEntity $application
     *
     * @return array containing total vehicles for application, other licences and other application
     */
    public function getTotalNumberOfAuthorisedVehicles($application)
    {
        // get the total vehicle authorisation for this application
        $appVehicles = $application->getTotAuthVehicles();

        // get the total vehicle authorisation for other licences
        $otherLicenceVehicles = 0;
        foreach ($application->getOtherActiveLicencesForOrganisation() as $licence) {
            $otherLicenceVehicles += (int)$licence->getTotAuthVehicles();
        }

        // get the total vehicle authorisation for other applications
        // that are 'under consideration' or 'granted'
        $otherApplicationVehicles = 0;
        $applications = $this->helper->getOtherNewApplications($application);
        foreach ($applications as $application) {
            $otherApplicationVehicles += (int)$application->getTotAuthVehicles();
        }

        return [
            'applicationVehicles' => $appVehicles,
            'otherLicenceVehicles' => $otherLicenceVehicles,
            'otherApplicationVehicles' => $otherApplicationVehicles,
        ];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return FinancialEvidence
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->helper = $container->get('FinancialStandingHelperService');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
