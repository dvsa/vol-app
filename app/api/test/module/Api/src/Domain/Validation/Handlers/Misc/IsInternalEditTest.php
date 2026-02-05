<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalEdit;

/**
 * Is Internal Edit Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class IsInternalEditTest extends AbstractHandlerTestCase
{
    /**
     * @var IsInternalEdit
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new IsInternalEdit();

        parent::setUp();
    }

    public function testIsValidInternalEdit(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::INTERNAL_EDIT, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidInternalEditFail(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::INTERNAL_EDIT, false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
