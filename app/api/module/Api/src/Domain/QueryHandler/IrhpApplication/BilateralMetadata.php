<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use DateTime;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata\CountryGenerator;
use Dvsa\Olcs\Api\Domain\Query\IrhpApplication\BilateralMetadata as BilateralMetadataQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Bilateral metadata
 */
class BilateralMetadata extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpApplication';

    protected $extraRepos = ['Country'];

    /** @var CountryGenerator */
    private $countryGenerator;

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $irhpApplicationId = $query->getIrhpApplication();

        $irhpApplication = new IrhpApplication();
        if (!is_null($irhpApplicationId)) {
            $irhpApplication = $this->getRepo()->fetchById($irhpApplicationId);
        }

        $countries = $this->getRepo('Country')->fetchAvailableCountriesForIrhpApplication(
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
            new DateTime()
        );

        $countryResponses = [];

        foreach ($countries as $country) {
            $countryResponses[] = $this->countryGenerator->generate($country, $irhpApplication);
        }

        return ['countries' => $countryResponses];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return BilateralMetadata
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->countryGenerator = $container->get('PermitsBilateralMetadataCountryGenerator');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
