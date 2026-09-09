<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Bus\Ebsr\CanSubmitEbsrPacks;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

final class CanSubmitEbsrPacksTest extends AbstractHandlerTestCase
{
    /**
     * @var CanSubmitEbsrPacks
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanSubmitEbsrPacks();

        parent::setUp();
    }

    public function testIsValidOperatorWithEbsrEligibleLicence(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, true);

        $organisation = m::mock(Organisation::class);
        $organisation->expects('hasEbsrEligibleLicence')->withNoArgs()->andReturnTrue();

        $this->mockOrganisation($organisation);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidOperatorWithoutEbsrEligibleLicence(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, true);

        $organisation = m::mock(Organisation::class);
        $organisation->expects('hasEbsrEligibleLicence')->withNoArgs()->andReturnFalse();

        $this->mockOrganisation($organisation);

        $this->assertFalse($this->sut->isValid($dto));
    }

    public function testIsValidOperatorWithNoOrganisation(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, true);

        $user = $this->mockUser();
        $user->shouldReceive('getOrganisationUsers->isEmpty')->andReturnTrue();
        $user->shouldReceive('getRelatedOrganisation')->never();

        $this->assertFalse($this->sut->isValid($dto));
    }

    public function testIsValidNonOperator(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, false);
        $this->setIsGranted(Permission::OPERATOR_TC, false);
        $this->setIsGranted(Permission::OPERATOR_USER, false);

        $this->assertFalse($this->sut->isValid($dto));
    }

    private function mockOrganisation(m\MockInterface $organisation): void
    {
        $user = $this->mockUser();
        $user->shouldReceive('getOrganisationUsers->isEmpty')->andReturnFalse();
        $user->shouldReceive('getRelatedOrganisation')->andReturn($organisation);
    }
}
