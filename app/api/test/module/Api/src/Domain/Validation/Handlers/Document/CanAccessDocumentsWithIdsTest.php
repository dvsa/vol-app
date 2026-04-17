<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Document;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Document\CanAccessDocumentsWithIds;

class CanAccessDocumentsWithIdsTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessDocumentsWithIdsTest
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessDocumentsWithIds();

        parent::setUp();
    }

    /**
     * Valid if all documents can be accessed
     */
    public function testIsValid(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getIds')->andReturn([76, 77, 78]);

        $this->setIsValid('canAccessDocument', [76], true);
        $this->setIsValid('canAccessDocument', [77], true);
        $this->setIsValid('canAccessDocument', [78], true);

        $this->assertSame(true, $this->sut->isValid($dto));
    }

    /**
     * Invalid if any of the documents cannot be accessed
     */
    public function testIsValidFailsIfOneIsInvalid(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getIds')->andReturn([76, 77, 78]);

        $this->setIsValid('canAccessDocument', [76], true);
        $this->setIsValid('canAccessDocument', [77], false);
        $this->setIsValid('canAccessDocument', [78], true);

        $this->assertSame(false, $this->sut->isValid($dto));
    }
}
