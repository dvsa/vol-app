<?php

/**
 * Can Access Application With Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessApplicationWithApplication;

/**
 * Can Access Application With Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessApplicationWithApplicationTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessApplicationWithApplication
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessApplicationWithApplication();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getApplication')->andReturn(111);

        $this->setIsValid('canAccessApplication', [111], $canAccess);

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
