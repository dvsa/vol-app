<?php

declare(strict_types=1);

/**
 * Can Access Organisation Person With Person Ids Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessOrganisationPersonWithIds;

/**
 * Can Access Organisation Person With Person Ids Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessOrganisationPersonWithIdsTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessOrganisationPersonWithIds
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessOrganisationPersonWithIds();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess1, mixed $canAccess2, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getIds')->andReturn([111, 222]);

        $this->setIsValid('canAccessOrganisationPerson', [111], $canAccess1);
        $this->setIsValid('canAccessOrganisationPerson', [222], $canAccess2);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function provider(): array
    {
        return [
            [
                true,
                true,
                true
            ],
            [
                false,
                false,
                false
            ],
            [
                true,
                false,
                false
            ],
            [
                false,
                true,
                false
            ]
        ];
    }
}
