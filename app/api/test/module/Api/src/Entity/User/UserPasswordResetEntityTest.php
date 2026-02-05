<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\User;

use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\User\UserPasswordReset as Entity;
use Mockery as m;

/**
 * UserPasswordReset Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class UserPasswordResetEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCreate(): void
    {
        $user = m::mock(User::class);
        $confirmation = 'confirmation';

        $sut = Entity::create($user, $confirmation);

        $this->assertEquals($user, $sut->getUser());
        $this->assertEquals($confirmation, $sut->getConfirmation());
        $this->assertFalse($sut->getSuccess());
        $this->assertInstanceOf(\DateTime::class, $sut->getValidTo());
    }

    public function testNotValidAlreadySuccess(): void
    {
        $user = m::mock(User::class);
        $user->expects('getLoginId')->never();
        $user->expects('canResetPassword')->never();

        $sut = Entity::create($user, 'confirmation');
        $sut->setSuccess(true);

        $this->assertFalse($sut->isValid('user'));
    }

    public function testNotValidDate(): void
    {
        $user = m::mock(User::class);
        $user->expects('getLoginId')->never();
        $user->expects('canResetPassword')->never();

        $sut = Entity::create($user, 'confirmation');
        $sut->setSuccess(false);

        $validTo = new \DateTime();
        $validTo->modify('-1 second');
        $sut->setValidTo($validTo);

        $this->assertFalse($sut->isValid('user'));
    }

    public function testNotValidLoginId(): void
    {
        $user = m::mock(User::class);
        $user->expects('getLoginId')->withNoArgs()->andReturn('user1');
        $user->expects('canResetPassword')->never();

        $sut = Entity::create($user, 'confirmation');
        $sut->setSuccess(false);

        $validTo = new \DateTime();
        $validTo->modify('+10 seconds');
        $sut->setValidTo($validTo);

        $this->assertFalse($sut->isValid('user2'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTrueFalseProvider')]
    public function testCanResetPassword(bool $canResetPassword): void
    {
        $loginId = 'user';
        $user = m::mock(User::class);
        $user->expects('getLoginId')->withNoArgs()->andReturn($loginId);
        $user->expects('canResetPassword')->withNoArgs()->andReturn($canResetPassword);

        $sut = Entity::create($user, 'confirmation');
        $sut->setSuccess(false);

        $validTo = new \DateTime();
        $validTo->modify('+10 seconds');
        $sut->setValidTo($validTo);

        $this->assertEquals($canResetPassword, $sut->isValid($loginId));
    }

    public static function dpTrueFalseProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
