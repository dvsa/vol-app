<?php

declare(strict_types=1);

/**
 * Can Access People With Person Ids Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessPeopleWithPersonIds;

/**
 * Can Access People With Person Ids Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CanAccessPeopleWithPersonIdsTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessPeopleWithPersonIds
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessPeopleWithPersonIds();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess1, mixed $canAccess2, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getPersonIds')->andReturn([111, 222]);

        $this->setIsValid('canAccessPerson', [111], $canAccess1);
        $this->setIsValid('canAccessPerson', [222], $canAccess2);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function provider(): \Iterator
    {
        yield [
            true,
            true,
            true
        ];
        yield [
            false,
            false,
            false
        ];
        yield [
            true,
            false,
            false
        ];
        yield [
            false,
            true,
            false
        ];
    }
}
