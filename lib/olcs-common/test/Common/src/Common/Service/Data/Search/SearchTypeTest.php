<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Data\Search;

use Common\Data\Object\Search\Application;
use Common\Data\Object\Search\Licence;
use Common\Data\Object\Search\User;
use Common\RefData;
use Common\Service\Data\Search\SearchType;
use Common\Service\Data\Search\SearchTypeManager;
use Common\Service\NavigationFactory;
use Psr\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use LmcRbacMvc\Service\RoleService;

/**
 * Class SearchTypeTest
 * @package CommonTest\Service\Data\Search
 */
class SearchTypeTest extends TestCase
{
    private function getMockSearchTypeManager(): m\MockInterface
    {
        $servicesArray = [
            0 => 'application',
            1 => 'licence',
            2 => 'user',
        ];

        $mockStm = m::mock(SearchTypeManager::class);
        $mockStm->shouldReceive('getRegisteredServices')->andReturn($servicesArray);
        $mockStm->shouldReceive('get')->with('application')->andReturn(new Application());
        $mockStm->shouldReceive('get')->with('licence')->andReturn(new Licence());
        $mockStm->shouldReceive('get')->with('user')->andReturn(new User());

        return $mockStm;
    }

    public function testGetNavigation(): void
    {
        $returnedNav = m::mock(Navigation::class);

        $mockNavFactory = m::mock(NavigationFactory::class);
        $mockNavFactory->shouldReceive('getNavigation')
            ->with(m::type('array'))
            ->andReturn($returnedNav);

        $mockRoleService = $this->getMockRoleService(false, [RefData::ROLE_INTERNAL_LIMITED_READ_ONLY]);

        $sut = new SearchType();
        $sut->setSearchTypeManager($this->getMockSearchTypeManager());
        $sut->setNavigationFactory($mockNavFactory);
        $sut->setRoleService($mockRoleService);

        $this->assertEquals($returnedNav, $sut->getNavigation());
    }

    public function testFetchListOptions(): void
    {
        $mockRoleService = $this->getMockRoleService(false, [RefData::ROLE_INTERNAL_LIMITED_READ_ONLY]);

        $sut = new SearchType();
        $sut->setSearchTypeManager($this->getMockSearchTypeManager());
        $sut->setRoleService($mockRoleService);

        $options = $sut->fetchListOptions(null);

        $this->assertCount(3, $options);
    }

    public function testFetchListOptionsForLimitedReadOnly(): void
    {
        $mockRoleService = $this->getMockRoleService(true, [RefData::ROLE_INTERNAL_LIMITED_READ_ONLY]);

        $sut = new SearchType();
        $sut->setSearchTypeManager($this->getMockSearchTypeManager());
        $sut->setRoleService($mockRoleService);

        $options = $sut->fetchListOptions(null);

        $this->assertCount(2, $options);
    }

    public function testInvoke(): void
    {
        $mockStm = $this->getMockSearchTypeManager();
        $mockRoleService = m::mock(RoleService::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with(SearchTypeManager::class)->andReturn($mockStm);
        $mockSl->shouldReceive('get')->with(RoleService::class)->andReturn($mockRoleService);

        $sut = new SearchType();
        $service = $sut->__invoke($mockSl, SearchType::class);

        $this->assertInstanceOf(SearchType::class, $service);
        $this->assertInstanceOf(NavigationFactory::class, $service->getNavigationFactory());
        $this->assertSame($mockStm, $service->getSearchTypeManager());
        $this->assertSame($mockRoleService, $service->getRoleService());
    }

    protected function getMockRoleService(bool $match, array $roles): m\MockInterface
    {
        $mockRoleService = m::mock(RoleService::class);
        $mockRoleService->shouldReceive('matchIdentityRoles')
            ->with($roles)
            ->andReturn($match);

        return $mockRoleService;
    }
}
