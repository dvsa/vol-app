<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Messaging;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging\CanListConversationsByOrganisation;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

class CanListConversationsTest extends AbstractHandlerTestCase
{
    /**
     * @var CanListConversationsByOrganisation
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanListConversationsByOrganisation();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsValid')]
    public function testIsValid(mixed $canAccess, mixed $hasPermission, mixed $expected): void
    {
        /** @var QueryInterface $dto */
        $orgId = 1;
        $permission = Permission::CAN_LIST_CONVERSATIONS;
        $dto = m::mock(QueryInterface::class);

        if ($canAccess) {
            $this->setIsGranted($permission, $hasPermission);
        }

        $dto->shouldReceive('getOrganisation')->once()->andReturn($orgId);

        $this->setIsValid('canAccessOrganisation', [$orgId], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function dpTestIsValid(): array
    {
        return [
            [true, true, true],
            [true, false, false],
            [false, true, false],
            [false, false, false],
        ];
    }
}
