<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Trailer;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessTrailer;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Trailer\DeleteTrailer;

/**
 * DeleteTrailerTest
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DeleteTrailerTest extends AbstractHandlerTestCase
{
    /**
     * @var DeleteTrailer
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new DeleteTrailer();

        parent::setUp();
    }

    public function testIsValidInternalUser(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getIds')->andReturn([19, 11, 2015]);

        $this->auth->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)->once()
            ->andReturn(true);

        $this->assertSame(true, $this->sut->isValid($dto));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getIds')->andReturn([19, 11, 2015]);

        $this->auth->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)->once()
            ->andReturn(false);

        $mockValidator = m::mock(CanAccessTrailer::class);
        $this->validatorManager->setService('canAccessTrailer', $mockValidator);

        $mockValidator->shouldReceive('isValid')->with(19)->andReturn($canAccess);
        $mockValidator->shouldReceive('isValid')->with(11)->andReturn($canAccess);
        $mockValidator->shouldReceive('isValid')->with(2015)->andReturn($canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    /**
     * @return array
     */
    public static function provider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
