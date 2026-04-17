<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Mvc;

use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Mvc\OlcsBlameableListener;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepository;
use Gedmo\Mapping\Event\AdapterInterface;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\ServiceManager\ServiceLocatorInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * OlcsBlameableListener Test
 */
class OlcsBlameableListenerTest extends MockeryTestCase
{
    /**
     * Holds the SUT
     *
     * @var OlcsBlameableListener
     */
    private $sut;

    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * Setup the sut
     */
    protected function setUp(): void
    {
        $this->serviceLocator = m::mock(ContainerInterface::class);
        $this->sut = new OlcsBlameableListener($this->serviceLocator);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getFieldValueDataProvider')]
    public function testGetFieldValue(mixed $currentUser, mixed $expected): void
    {
        /** @var AuthorizationService $mockAuth */
        $mockAuth = m::mock(AuthorizationService::class);
        $mockAuth->shouldReceive('getIdentity->getUser')->andReturn($currentUser);

        $mockUserRepo = m::mock(UserRepository::class);

        $this->serviceLocator
            ->shouldReceive('get')
            ->with(AuthorizationService::class)
            ->andReturn($mockAuth)
            ->once()
            ->shouldReceive('get')
            ->with('RepositoryServiceManager')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('User')
                ->andReturn($mockUserRepo)
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('get')
            ->with(IdentityProviderInterface::class)
            ->andReturn(
                m::mock(IdentityProviderInterface::class)
                ->shouldReceive('getMasqueradedAsSystemUser')
                ->andReturn(false)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $meta = m::mock(\stdClass::class);
        $meta->shouldReceive('hasAssociation')->once()->andReturn(true);
        $field = 'field';
        $eventAdapter = m::mock(AdapterInterface::class);

        $this->assertSame(
            $expected,
            $this->sut->getFieldValue($meta, $field, $eventAdapter)
        );
    }

    public function testGetFieldValueMasqueraded(): void
    {
        $mockUser = User::create(
            'abc',
            User::USER_TYPE_OPERATOR,
            ['loginId' => 'loginId']
        );

        /** @var AuthorizationService $mockAuth */
        $mockAuth = m::mock(AuthorizationService::class);

        $mockUserRepo = m::mock(UserRepository::class)
            ->shouldReceive('fetchById')
            ->with(IdentityProviderInterface::SYSTEM_USER)
            ->andReturn($mockUser)
            ->once()
            ->getMock();

        $this->serviceLocator
            ->shouldReceive('get')
            ->with(AuthorizationService::class)
            ->andReturn($mockAuth)
            ->once()
            ->shouldReceive('get')
            ->with('RepositoryServiceManager')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('User')
                    ->andReturn($mockUserRepo)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('get')
            ->with(IdentityProviderInterface::class)
            ->andReturn(
                m::mock(IdentityProviderInterface::class)
                    ->shouldReceive('getMasqueradedAsSystemUser')
                    ->andReturn(true)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $meta = m::mock(\stdClass::class);
        $meta->shouldReceive('hasAssociation')->once()->andReturn(true);
        $field = 'field';
        $eventAdapter = m::mock(AdapterInterface::class);

        $this->assertSame(
            $mockUser,
            $this->sut->getFieldValue($meta, $field, $eventAdapter)
        );
    }

    public static function getFieldValueDataProvider(): array
    {
        $mockUser = User::create(
            'abc',
            User::USER_TYPE_OPERATOR,
            ['loginId' => 'loginId']
        );

        return [
            [$mockUser, $mockUser],
            [User::anon(), null],
            [null, null],
        ];
    }
}
