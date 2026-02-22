<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Organisation;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as PermitStockRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitType as PermitTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as PermitWindowRepo;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as PermitStockEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as PermitTypeEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow as PermitWindowEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockAvailabilityChecker;
use Dvsa\Olcs\Transfer\Query\Organisation\OrganisationAvailableLicences as OrganisationPermitsQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Organisation for Permits
 */
class OrganisationAvailableLicences extends AbstractQueryHandler
{
    public const ERR_TYPE_MISMATCH = 'Permit type does not match the stock';

    protected $repoServiceName = 'Organisation';
    protected $extraRepos = ['IrhpPermitStock', 'IrhpPermitType', 'IrhpPermitWindow'];

    /** @var StockAvailabilityChecker */
    private $stockAvailabilityChecker;

    /**
     * Handle query
     *
     * @param QueryInterface|OrganisationPermitsQry $query query
     *
     * @return array
     * @throws \Exception
     */
    public function handleQuery(QueryInterface $query)
    {
        $permitTypeId = $query->getIrhpPermitType();
        $permitStockId = $query->getIrhpPermitStock();

        if ($permitStockId !== null) {
            /**
             * @var PermitStockRepo   $permitStockRepo
             * @var PermitStockEntity $permitStock
             */
            $permitStockRepo = $this->getRepo('IrhpPermitStock');
            $permitStock = $permitStockRepo->fetchById($permitStockId);

            if ($permitStock->getIrhpPermitType()->getId() !== $permitTypeId) {
                throw new \Exception(self::ERR_TYPE_MISMATCH);
            }
        }

        /**
         * @var PermitTypeRepo     $permitTypeRepo
         * @var PermitTypeEntity   $permitType
         * @var OrganisationRepo   $organisationRepo
         * @var OrganisationEntity $organisation
         */
        $permitTypeRepo = $this->getRepo('IrhpPermitType');
        $permitType = $permitTypeRepo->fetchById($permitTypeId);

        $organisationRepo = $this->getRepo('Organisation');
        $organisation = $organisationRepo->fetchUsingId($query);

        if ($permitStockId === null && $permitType->usesMultiStockLicenceBehaviour()) {
            return $this->multiStock($organisation, $permitTypeId);
        }

        //most permits types we assume permits are available
        $permitsAvailable = true;

        if ($permitType->isEcmtShortTerm()) {
            $permitsAvailable = $this->stockAvailabilityChecker->hasAvailability($permitStockId);
        }

        $eligibleLicences = $organisation->getEligibleIrhpLicencesForStock($permitStock);

        return [
            'hasOpenWindow' => $permitStock->hasOpenWindow(),
            'isEcmtAnnual' => $permitType->isEcmtAnnual(),
            'isBilateral' => $permitType->isBilateral(),
            'permitTypeId' => $permitTypeId,
            'eligibleLicences' => $eligibleLicences,
            'hasEligibleLicences' => !empty($eligibleLicences),
            'permitsAvailable' => $permitsAvailable,
            'selectedLicence' => null,
        ];
    }

    /**
     * To preserve existing (broken) behaviour, we do things differently for permit types with multiple stocks
     *
     *
     * @return array
     */
    private function multiStock(OrganisationEntity $organisation, int $permitTypeId): array
    {
        /**
         * @var PermitWindowRepo   $permitWindowRepo
         * @var PermitWindowEntity $window
         */
        $permitWindowRepo = $this->getRepo('IrhpPermitWindow');
        $hasOpenWindow = true;

        try {
            $window = $permitWindowRepo->fetchLastOpenWindowByIrhpPermitType(
                $permitTypeId,
                new \DateTime()
            );

            $eligibleLicences = $organisation->getEligibleIrhpLicencesForStock($window->getIrhpPermitStock());
        } catch (NotFoundException) {
            $hasOpenWindow = false;
            $eligibleLicences = [];
        }

        /**
         * @var PermitTypeRepo     $permitTypeRepo
         * @var PermitTypeEntity   $permitType
         */
        $permitTypeRepo = $this->getRepo('IrhpPermitType');
        $permitType = $permitTypeRepo->fetchById($permitTypeId);

        return [
            'hasOpenWindow' => $hasOpenWindow,
            'isEcmtAnnual' => false,
            'isBilateral' => $permitType->isBilateral(),
            'permitTypeId' => $permitTypeId,
            'eligibleLicences' => $eligibleLicences,
            'hasEligibleLicences' => !empty($eligibleLicences),
            'permitsAvailable' => true, //we don't check this yet for these stocks
            'selectedLicence' => null,
        ];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return OrganisationAvailableLicences
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->stockAvailabilityChecker = $container->get('PermitsAvailabilityStockAvailabilityChecker');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
