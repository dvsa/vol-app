<?php

declare(strict_types=1);

/**
 * Validation Helper Test Case Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation;

use Dvsa\Olcs\Api\Domain\Repository\RepositoryInterface;
use Dvsa\Olcs\Api\Domain\Validation\Validators\ValidatorInterface;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Laminas\ServiceManager\ServiceManager;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\ValidatorManager;
use Mockery\MockInterface;

/**
 * Validation Helper Test Case Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait ValidationHelperTestCaseTrait
{
    /**
     * @var RepositoryServiceManager
     */
    protected $repoManager;

    /**
     * @var ValidatorManager
     */
    protected $validatorManager;

    /**
     * @var AuthorizationService | MockInterface
     */
    protected $auth;

    protected $cache;

    public function setUp(): void
    {
        $this->repoManager = m::mock(RepositoryServiceManager::class);
        $this->auth = m::mock(AuthorizationService::class);
        $this->cache = m::mock(CacheEncryption::class);
        $this->validatorManager = m::mock(ValidatorManager::class)->makePartial();

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('RepositoryServiceManager', $this->repoManager);
        $sm->setService(AuthorizationService::class, $this->auth);
        $sm->setService('DomainValidatorManager', $this->validatorManager);
        $sm->setService(CacheEncryption::class, $this->cache);
        $sm->setService('config', ['config']);

        $this->sut->__invoke($sm, null);
    }

    public function mockRepo(mixed $repoName): m\MockInterface
    {
        $mockRepo = m::mock(RepositoryInterface::class);
        $this->repoManager->shouldReceive('get')->with($repoName)->andReturn($mockRepo);

        return $mockRepo;
    }

    public function mockUser(): mixed
    {
        $user = m::mock(User::class);
        $this->auth->shouldReceive('getIdentity->getUser')->andReturn($user);

        return $user;
    }

    public function setIsGranted(mixed $permission, mixed $return, mixed $context = null): void
    {
        $this->auth->shouldReceive('isGranted')->with($permission, $context)->once()->andReturn($return);
    }

    public function setIsValid(mixed $validator, mixed $arguments, bool $isValid = true): void
    {
        if ($this->validatorManager->has($validator) === false) {
            $mockValidator = m::mock(ValidatorInterface::class);
            $this->validatorManager->setService($validator, $mockValidator);
        } else {
            $mockValidator = $this->validatorManager->get($validator);
        }

        $mockValidator->shouldReceive('isValid')->withArgs($arguments)->andReturn($isValid);
    }
}
