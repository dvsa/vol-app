<?php

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

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccessOrganisation, $expected)
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $dto = \Dvsa\Olcs\Transfer\Query\User\UserList::create(['organisation' => 76]);

        $this->setIsValid('canAccessOrganisation', [76], $canAccessOrganisation);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function testIsValidInternal()
    {
        $this->setIsGranted(Permission::INTERNAL_USER, true);

        $dto = \Dvsa\Olcs\Transfer\Query\User\UserList::create([]);

        $this->assertEquals(true, $this->sut->isValid($dto));
    }

    public function testIsValidWithoutOrg()
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $dto = \Dvsa\Olcs\Transfer\Query\User\UserList::create([]);

        $this->assertSame(false, $this->sut->isValid($dto));
    }

    public function provider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
