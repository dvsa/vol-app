<?php

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

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccess, $expected)
    {
        $dto = \Dvsa\Olcs\Transfer\Command\User\UpdateUserSelfserve::create(['id' => 76]);

        $this->setIsValid('canManageUser', [76], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidWithoutId($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = \Dvsa\Olcs\Transfer\Command\User\CreateUserSelfserve::create([]);

        $this->setIsValid('canManageUser', [null], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function provider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
