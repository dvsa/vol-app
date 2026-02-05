<?php

declare(strict_types=1);

namespace OlcsTest\Controller;

use Common\Rbac\User;
use Common\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\IndexController;

/**
 * Class Index Controller Test
 */
class IndexControllerTest extends MockeryTestCase
{
    /** @var IndexController|\Mockery\MockInterface  */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(IndexController::class)
            ->makePartial();
    }

    public function testIndexLogin(): void
    {
        $this->sut->shouldReceive('currentUser->getIdentity')
            ->once()
            ->andReturnNull();

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('auth/login/GET')
            ->once()
            ->andReturn('REDIRECT');

        static::assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexLoginAnon(): void
    {
        $mockIdentity = new User();
        $mockIdentity->setUserType(User::USER_TYPE_ANON);

        $this->sut->shouldReceive('currentUser->getIdentity')
            ->once()
            ->andReturn($mockIdentity);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('auth/login/GET')
            ->once()
            ->andReturn('REDIRECT');

        static::assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexLoginNotIdentified(): void
    {
        $mockIdentity = new User();
        $mockIdentity->setUserType(User::USER_TYPE_NOT_IDENTIFIED);

        $this->sut->shouldReceive('currentUser->getIdentity')
            ->once()
            ->andReturn($mockIdentity);

        $this->expectException(\Exception::class);

        $this->sut->indexAction();
    }

    public function testIndexDashboard(): void
    {
        $mockIdentity = new User();
        $mockIdentity->setUserType(User::USER_TYPE_OPERATOR);
        $mockIdentity->setUserData(['eligibleForPrompt' => false]);

        $this->sut->shouldReceive('currentUser->getIdentity')
            ->once()
            ->andReturn($mockIdentity);

        $this->sut->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_SELFSERVE_DASHBOARD)
            ->andReturn(true);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('dashboard', [], ['code' => 303])
            ->once()
            ->andReturn('REDIRECT');

        static::assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexPrompt(): void
    {
        $mockIdentity = new User();
        $mockIdentity->setUserType(User::USER_TYPE_OPERATOR);
        $mockIdentity->setUserData(['eligibleForPrompt' => true]);

        $this->sut->shouldReceive('currentUser->getIdentity')
            ->once()
            ->andReturn($mockIdentity);

        $this->sut->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_SELFSERVE_DASHBOARD)
            ->andReturn(true);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('prompt', [], ['code' => 303])
            ->once()
            ->andReturn('REDIRECT');

        static::assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexBusReg(): void
    {
        $mockIdentity = new User();
        $mockIdentity->setUserType(User::USER_TYPE_LOCAL_AUTHORITY);

        $this->sut->shouldReceive('currentUser->getIdentity')
            ->once()
            ->andReturn($mockIdentity);

        $this->sut->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_SELFSERVE_DASHBOARD)
            ->andReturn(false);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('busreg-registrations', [], ['code' => 303])
            ->once()
            ->andReturn('REDIRECT');

        static::assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexSearch(): void
    {
        $mockIdentity = new User();
        $mockIdentity->setUserType(User::USER_TYPE_OPERATOR);

        $this->sut->shouldReceive('currentUser->getIdentity')
            ->once()
            ->andReturn($mockIdentity);

        $this->sut->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_SELFSERVE_DASHBOARD)
            ->andReturn(false);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('search')
            ->once()
            ->andReturn('REDIRECT');

        static::assertEquals('REDIRECT', $this->sut->indexAction());
    }
}
