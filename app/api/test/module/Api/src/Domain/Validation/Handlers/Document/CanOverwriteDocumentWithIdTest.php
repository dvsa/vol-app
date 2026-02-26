<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Document;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Document\CanOverwriteDocumentWithId;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

class CanOverwriteDocumentWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanOverwriteDocumentWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanOverwriteDocumentWithId();

        parent::setUp();
    }

    public function testIsValidWhenCanAccessAndIsCreator(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(76);

        $this->setIsValid('canAccessDocument', [76], true);
        $this->setIsValid('isDocumentCreator', [76], true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsInvalidWhenCannotAccess(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(76);

        $this->setIsValid('canAccessDocument', [76], false);

        $this->assertFalse($this->sut->isValid($dto));
    }

    public function testIsInvalidWhenCanAccessButNotCreator(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(76);

        $this->setIsValid('canAccessDocument', [76], true);
        $this->setIsValid('isDocumentCreator', [76], false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
