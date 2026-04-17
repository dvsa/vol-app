<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\User;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\User\CanAccessUserList as Sut;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Handlers\User\CanAccessUserList
 */
class CanAccessUserListTest extends AbstractHandlerTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Sut();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccessOrganisation, mixed $expected): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $dto = \Dvsa\Olcs\Transfer\Query\User\UserList::create(['organisation' => 76]);

        $this->setIsValid('canAccessOrganisation', [76], $canAccessOrganisation);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function testIsValidInternal(): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, true);

        $dto = \Dvsa\Olcs\Transfer\Query\User\UserList::create([]);

        $this->assertEquals(true, $this->sut->isValid($dto));
    }

    public function testIsValidWithoutOrg(): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $dto = \Dvsa\Olcs\Transfer\Query\User\UserList::create([]);

        $this->assertSame(false, $this->sut->isValid($dto));
    }

    public static function provider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
