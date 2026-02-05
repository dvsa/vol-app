<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\DvlaSearch;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\BadResponseException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\ServiceException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\VehicleUnavailableException;
use Dvsa\Olcs\Api\Service\DvlaSearch\DvlaSearchService;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\DvlaSearch\Vehicle as VehicleQuery;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Vehicle extends AbstractQueryHandler
{
    /**
     * @var DvlaSearchService
     */
    protected $dvlaSearchService;

    /**
     * @param VehicleQuery|QueryInterface $query
     * @return array<string,array<mixed>>
     * @throws ServiceException
     * @throws BadResponseException
     * @throws GuzzleException
     */
    public function handleQuery(QueryInterface $query)
    {
        try {
            $vehicle = $this->dvlaSearchService->getVehicle($query->getVrm());
            return [
                'count' => 1,
                'result' => [$vehicle->toArray()]
            ];
        } catch (VehicleUnavailableException) {
            return [
                'count' => 0,
                'result' => []
            ];
        }
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Vehicle
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->dvlaSearchService = $container->get(DvlaSearchService::class);
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
