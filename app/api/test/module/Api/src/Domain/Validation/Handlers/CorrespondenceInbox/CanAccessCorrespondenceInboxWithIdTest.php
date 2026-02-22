<?php

declare(strict_types=1);

/**
 * Can Access Xoc With Reference Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\CorrespondenceInbox;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\CorrespondenceInbox\CanAccessCorrespondenceInboxWithId;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Handlers\CorrespondenceInbox\CanAccessCorrespondenceInboxWithId
 */
class CanAccessCorrespondenceInboxWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessCorrespondenceInboxWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessCorrespondenceInboxWithId();

        parent::setUp();
    }

    /**
     *
     * @param $canAccess
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(76);

        $this->setIsValid('canAccessCorrespondenceInbox', [76], $canAccess);

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
