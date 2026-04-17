<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query;

use Doctrine\DBAL\Result as DbalResult;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Mockery as m;

/**
 * Abstract Db Query Test Case
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractDbQueryTestCase extends BaseAbstractDbQueryTestCase
{
    abstract public static function paramProvider(): array;

    #[\PHPUnit\Framework\Attributes\DataProvider('paramProvider')]
    public function testExecuteWithException(mixed $inputParams, mixed $inputTypes, mixed $expectedParams, mixed $expectedTypes): void
    {
        $this->mockIdentityProvider
            ->shouldReceive('getMasqueradedAsSystemUser')
            ->andReturn(false);

        // add generic params
        $expectedParams['currentUserId'] = 1;

        $this->expectException(RuntimeException::class);

        $this->connection->shouldReceive('executeQuery')
            ->with($this->getExpectedQuery(), $expectedParams, $expectedTypes)
            ->once()
            ->andThrow(new \Exception());

        $this->sut->execute($inputParams, $inputTypes);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('paramProvider')]
    public function testExecute(mixed $inputParams, mixed $inputTypes, mixed $expectedParams, mixed $expectedTypes): void
    {
        $this->mockIdentityProvider
            ->shouldReceive('getMasqueradedAsSystemUser')
            ->andReturn(false);

        // add generic params
        $expectedParams['currentUserId'] = 1;

        $result = m::mock(DbalResult::class);

        $this->connection->shouldReceive('executeQuery')
            ->with($this->getExpectedQuery(), $expectedParams, $expectedTypes)
            ->once()
            ->andReturn($result);

        $this->assertEquals($result, $this->sut->execute($inputParams, $inputTypes));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('paramProvider')]
    public function testExecuteAsSystemUser(mixed $inputParams, mixed $inputTypes, mixed $expectedParams, mixed $expectedTypes): void
    {
        $this->mockIdentityProvider
            ->shouldReceive('getMasqueradedAsSystemUser')
            ->andReturn(true);

        $user = m::mock(UserEntity::class)->makePartial();
        $user->setId(IdentityProviderInterface::SYSTEM_USER);

        $this->mockUserRepo
            ->shouldReceive('fetchById')
            ->with(IdentityProviderInterface::SYSTEM_USER)
            ->andReturn($user);

        // add generic params
        $expectedParams['currentUserId'] = IdentityProviderInterface::SYSTEM_USER;

        $result = m::mock(DbalResult::class);

        $this->connection->shouldReceive('executeQuery')
            ->with($this->getExpectedQuery(), $expectedParams, $expectedTypes)
            ->once()
            ->andReturn($result);

        $this->assertEquals($result, $this->sut->execute($inputParams, $inputTypes));
    }
}
