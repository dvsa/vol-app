<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Permits;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Permits\CanAccessIrhpApplicationWithIrhpApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Handlers\Permits\CanAccessIrhpApplicationWithIrhpApplication
 */
class CanAccessIrhpApplicationWithIrhpApplicationTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessIrhpApplicationWithIrhpApplication
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessIrhpApplicationWithIrhpApplication();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsValid')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $id = 111;
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getIrhpApplication')->andReturn($id);

        $this->setIsValid('canAccessIrhpApplicationWithId', [$id], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function dpTestIsValid(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
