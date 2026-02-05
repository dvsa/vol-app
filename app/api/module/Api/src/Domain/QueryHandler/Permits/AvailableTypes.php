<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitType as IrhpPermitTypeRepo;
use Dvsa\Olcs\Transfer\Query\Permits\AvailableTypes as AvailableTypesQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Service\Permits\Availability\WindowAvailabilityChecker;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use DateTime;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Available types
 */
class AvailableTypes extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitType';

    /** @var WindowAvailabilityChecker */
    private $windowAvailabilityChecker;

    /**
     * Handle query
     *
     * @param QueryInterface|AvailableTypesQuery $query query
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleQuery(QueryInterface $query)
    {
        $now = new DateTime();

        /** @var IrhpPermitTypeRepo $repo */
        $repo = $this->getRepo('IrhpPermitType');
        $availableTypes = $repo->fetchAvailableTypes($now);

        $filteredAvailableTypes = [];
        foreach ($availableTypes as $type) {
            $includeType = true;
            if ($type->isEcmtShortTerm()) {
                $includeType = $this->windowAvailabilityChecker->hasAvailability($now);
            }

            if ($includeType) {
                $filteredAvailableTypes[] = $type;
            }
        }

        return [
            'types' => $filteredAvailableTypes,
            'hasTypes' => !empty($filteredAvailableTypes),
        ];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return AvailableTypes
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->windowAvailabilityChecker = $container->get('PermitsAvailabilityWindowAvailabilityChecker');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
