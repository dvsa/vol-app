<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NotIsAnonymousUser;

/**
 * Not ss Anonymous User Test
 */
class NotIsAnonymousUserTest extends AbstractHandlerTestCase
{
    /**
     * @var NotIsAnonymousUser
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new NotIsAnonymousUser();

        parent::setUp();
    }

    /**
     * Tests whether the user is not anonymous
     *
     *
     * @param bool $isAnonymous whether the user is anonymous
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isAnonymousUserProvider')]
    public function testNotIsAnonymousUser(mixed $isAnonymous): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $mockUser = $this->mockUser();
        $mockUser->shouldReceive('isAnonymous')
            ->andReturn($isAnonymous)
            ->once();

        $this->assertEquals(!$isAnonymous, $this->sut->isValid($dto));
    }

    /**
     * Returns true or false based on whether user is anonymous
     *
     * @return array
     */
    public static function isAnonymousUserProvider(): array
    {
        return[
            [true],
            [false]
        ];
    }
}
