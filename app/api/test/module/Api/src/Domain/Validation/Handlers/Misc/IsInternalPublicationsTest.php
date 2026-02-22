<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalPublications;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

/**
 * IsInternalPublications Test
 */
class IsInternalPublicationsTest extends AbstractHandlerTestCase
{
    /**
     * @var IsInternalPublications
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new IsInternalPublications();

        parent::setUp();
    }

    public function testIsValidInternalPublications(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::INTERNAL_PUBLICATIONS, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidInternalPublicationsFail(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::INTERNAL_PUBLICATIONS, false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
