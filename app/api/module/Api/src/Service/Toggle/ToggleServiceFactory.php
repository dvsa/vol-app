<?php

namespace Dvsa\Olcs\Api\Service\Toggle;

use Dvsa\Olcs\Api\Domain\Repository\FeatureToggle as FeatureToggleRepo;
use Dvsa\Olcs\Api\Domain\Query\FeatureToggle\FetchList;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Qandidate\Toggle\Serializer\InMemoryCollectionSerializer;
use Qandidate\Toggle\ToggleManager;
use Psr\Container\ContainerInterface;

class ToggleServiceFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ToggleService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ToggleService
    {
        /**
         * @var FeatureToggleRepo $repo
         */
        $repo = $container->get('RepositoryServiceManager')->get('FeatureToggle');
        $toggleConfig = $repo->fetchList(FetchList::create([]));
        $configArray = [];
        foreach ($toggleConfig as $toggle) {
            $configArray[$toggle['friendlyName']] = [
                'name' => $toggle['configName'],
                'conditions' => [],
                'status' => $toggle['status']['id']
            ];
        }
        $collectionSerializer = new InMemoryCollectionSerializer();
        $collection = $collectionSerializer->deserialize($configArray);
        return new ToggleService(
            new ToggleManager($collection)
        );
    }
}
