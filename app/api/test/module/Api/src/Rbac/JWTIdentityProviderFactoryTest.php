<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Rbac;

use Dvsa\Authentication\Cognito\Client;
use Dvsa\Contracts\Auth\OAuthClientInterface;
use Dvsa\Olcs\Api\Domain\Repository\User;
use Dvsa\Olcs\Api\Rbac\JWTIdentityProvider;
use Dvsa\Olcs\Api\Rbac\JWTIdentityProviderFactory;
use Laminas\Http\Request;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\OlcsTest\MocksServicesTrait;

/**
 * Class JWTIdentityProviderFactoryTest
 * @see JWTIdentityProviderFactory
 */
class JWTIdentityProviderFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var JWTIdentityProviderFactory
     */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

    /**
     * @test
     */
    public function invokeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable($this->sut->__invoke(...));
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeReturnsAnInstanceOfJWTIdentityProvider()
    {
        // Setup
        $this->setUpSut();

        // Expectations
        $repositoryServiceManager = $this->repositoryServiceManager();
        $repositoryServiceManager->expects('get')->with('User')->andReturn(m::mock(User::class));

        // Execute
        $result = $this->sut->__invoke($this->serviceManager(), null);

        // Assert
        $this->assertInstanceOf(JWTIdentityProvider::class, $result);
    }

    protected function setUpSut(): void
    {
        $this->sut = new JWTIdentityProviderFactory();
    }

    protected function setUpDefaultServices(ServiceManager $serviceManager)
    {
        $this->repositoryServiceManager();
        $this->serviceManager()->setService('Request', m::mock(Request::class));
        $this->serviceManager->setService(OAuthClientInterface::class, m::mock(Client::class));
    }

    private function repositoryServiceManager()
    {
        if (!$this->serviceManager->has('RepositoryServiceManager')) {
            $instance = $this->setUpMockService(RepositoryServiceManager::class);
            $this->serviceManager->setService('RepositoryServiceManager', $instance);
        }
        $instance = $this->serviceManager->get('RepositoryServiceManager');

        return $instance;
    }
}
