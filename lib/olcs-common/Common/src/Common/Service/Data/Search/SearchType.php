<?php

namespace Common\Service\Data\Search;

use Common\Data\Object\Search\User;
use Common\RefData;
use Common\Service\Data\Interfaces\ListData as ListDataInterface;
use Common\Service\NavigationFactory;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\RoleService;
use Psr\Container\ContainerInterface;

class SearchType implements ListDataInterface, FactoryInterface
{
    protected SearchTypeManager $searchTypeManager;
    protected NavigationFactory $navigationFactory;
    protected RoleService $roleService;

    public function getSearchTypeManager(): SearchTypeManager
    {
        return $this->searchTypeManager;
    }

    public function setSearchTypeManager(SearchTypeManager $searchTypeManager): void
    {
        $this->searchTypeManager = $searchTypeManager;
    }

    public function getNavigationFactory(): NavigationFactory
    {
        return $this->navigationFactory;
    }

    public function setNavigationFactory(NavigationFactory $navigationFactory): void
    {
        $this->navigationFactory = $navigationFactory;
    }

    public function getRoleService(): RoleService
    {
        return $this->roleService;
    }

    public function setRoleService(RoleService $authorizationService): void
    {
        $this->roleService = $authorizationService;
    }

    /**
     * Fetch back a set of options for a drop down list, context passed is parameters which may need to be passed to the
     * back end to filter the result set returned, use groups when specified should, cause this method to return the
     * data as a multi dimensioned array suitable for display in opt-groups. It is permissible for the method to ignore
     * this flag if the data doesn't allow for option groups to be constructed.
     *
     * @param mixed $context
     * @param bool $useGroups
     * @return array
     */
    #[\Override]
    public function fetchListOptions($context, $useGroups = false)
    {
        $options = [];

        foreach ($this->getSearchTypes() as $searchIndex) {
            /** @var $searchIndex \Common\Data\Object\Search\SearchAbstract  */
            if ($context === null || $searchIndex->getDisplayGroup() === $context) {
                $options[$searchIndex->getKey()] = $searchIndex->getTitle();
            }
        }

        return $options;
    }

    protected function getSearchTypes(): array
    {
        $services = $this->getSearchTypeManager()->getRegisteredServices();

        $indexes = [];

        foreach ($services as $searchIndexName) {
            $indexes[] = $this->getSearchTypeManager()->get($searchIndexName);
        }

        if ($this->roleService->matchIdentityRoles([RefData::ROLE_INTERNAL_LIMITED_READ_ONLY])) {
            return array_filter($indexes, static fn($value, $key) => !($value instanceof User), ARRAY_FILTER_USE_BOTH);
        }

        return $indexes;
    }

    /**
     * @psalm-param 'internal-search'|null $context
     */
    public function getNavigation($context = null, array $queryParams = []): Navigation
    {
        $nav = [];
        foreach ($this->getSearchTypes() as $searchIndex) {
            /** @var \Common\Data\Object\Search\SearchAbstract $searchIndex */
            if ($context === null || $searchIndex->getDisplayGroup() === $context) {
                $nav[] = $searchIndex->getNavigation($queryParams);
            }
        }

        return $this->getNavigationFactory()->getNavigation($nav);
    }

    /**
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SearchType
    {
        $this->setNavigationFactory(new NavigationFactory($container));
        $this->setRoleService($container->get(RoleService::class));
        $this->setSearchTypeManager($container->get(SearchTypeManager::class));
        return $this;
    }
}
