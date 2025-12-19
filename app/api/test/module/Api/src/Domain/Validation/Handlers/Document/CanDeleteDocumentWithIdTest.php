<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Document;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Document\CanDeleteDocumentWithId;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

class CanDeleteDocumentWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanDeleteDocumentWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanDeleteDocumentWithId();

        parent::setUp();
    }

    /**
     * Valid if document can be accessed and deleted
     */
    public function testIsValidIfCanAccessAndCanDelete()
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(76);

        $this->setIsValid('canAccessDocument', [76], true);
        $this->setIsValid('canDeleteDocument', [76], true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    /**
     * Invalid if the document can be deleted, but cannot be accessed
     */
    public function testIsValidIfCannotAccessAndCanDelete()
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(76);

        $this->setIsValid('canAccessDocument', [76], false);
        $this->setIsValid('canDeleteDocument', [76], true);

        $this->assertFalse($this->sut->isValid($dto));
    }

    /**
     * Invalid if the document can be accessed, but cannot be deleted
     */
    public function testIsValidIfCanAccessAndCannotDelete()
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(76);

        $this->setIsValid('canAccessDocument', [76], true);
        $this->setIsValid('canDeleteDocument', [76], false);

        $this->assertFalse($this->sut->isValid($dto));
    }

    /**
     * Invalid if the document cannot be deleted and cannot be accessed
     */
    public function testIsValidIfCannotAccessAndCannotDelete()
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(76);

        $this->setIsValid('canAccessDocument', [76], false);
        $this->setIsValid('canDeleteDocument', [76], false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
