<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\User;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\User\CanManageUser as Sut;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Handlers\User\CanManageUser
 */
class CanManageUserTest extends AbstractHandlerTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Sut();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        $dto = \Dvsa\Olcs\Transfer\Command\User\UpdateUserSelfserve::create(['id' => 76]);

        $this->setIsValid('canManageUser', [76], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValidWithoutId(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = \Dvsa\Olcs\Transfer\Command\User\CreateUserSelfserve::create([]);

        $this->setIsValid('canManageUser', [null], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function provider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
