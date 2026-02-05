<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use DateTime;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockAvailabilityChecker;
use Dvsa\Olcs\Transfer\Query\Permits\AvailableYears as AvailableYearsQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Available years
 */
class AvailableYears extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitWindow';

    /** @var StockAvailabilityChecker */
    private $stockAvailabilityChecker;

    /**
     * Handle query
     *
     * @param QueryInterface|AvailableYearsQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $permitType = $query->getType();

        $applicableTypes = [
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
        ];

        if (!in_array($permitType, $applicableTypes)) {
            return [
                'hasYears' => false,
                'years' => []
            ];
        }

        $openWindows = $this->getRepo()->fetchOpenWindowsByType(
            $permitType,
            new DateTime(),
            $this->isInternalUser()
        );

        $availableYears = [];
        foreach ($openWindows as $window) {
            $irhpPermitStock = $window->getIrhpPermitStock();
            $irhpPermitStockId = $irhpPermitStock->getId();

            $includeYear = true;
            if ($permitType == IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM) {
                $includeYear = $this->stockAvailabilityChecker->hasAvailability($irhpPermitStockId);
            }

            if ($includeYear) {
                $availableYears[$irhpPermitStockId] = $irhpPermitStock->getValidityYear();
            }
        }

        $availableYears = array_unique($availableYears);
        asort($availableYears);

        return [
            'hasYears' => (count($availableYears) > 0),
            'years' => $availableYears,
        ];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return AvailableYears
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
