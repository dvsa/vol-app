<?php

declare(strict_types=1);

/**
 * Delete Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\CompanySubsidiary\Application;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\CompanySubsidiary\Application\Delete;
use Laminas\ServiceManager\ServiceManager;
use Dvsa\Olcs\Transfer\Command\Application\DeleteCompanySubsidiary as Cmd;

/**
 * Delete Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DeleteTest extends AbstractHandlerTestCase
{
    /**
     * @var Delete
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Delete();

        parent::setUp();
    }

    public function testIsValidNoContext(): void
    {
        $data = [
            'application' => null
        ];

        $dto = Cmd::create($data);

        $this->assertFalse($this->sut->isValid($dto));
    }

    public function testIsValidWithContextNoAccess(): void
    {
        $data = [
            'ids' => [111],
            'application' => 222
        ];

        $licence = $this->getLicenceFromApplication();
        $licence->shouldReceive('getId')->andReturn(123);

        $dto = Cmd::create($data);

        $this->setIsValid('canAccessLicence', [123], false);

        $this->assertEquals(false, $this->sut->isValid($dto));
    }

    public function testIsValidWithContextNoOwnership(): void
    {
        $data = [
            'ids' => [111],
            'application' => 222
        ];

        $licence = $this->getLicenceFromApplication();
        $licence->shouldReceive('getId')->andReturn(123);

        $dto = Cmd::create($data);

        $this->setIsValid('canAccessCompanySubsidiary', [111], false);
        $this->setIsValid('canAccessLicence', [123], true);

        $this->assertEquals(false, $this->sut->isValid($dto));
    }

    public function testIsValidWithContextAndOwnership(): void
    {
        $data = [
            'ids' => [111],
            'application' => 222
        ];

        $dto = Cmd::create($data);

        $licence = $this->getLicenceFromApplication();
        $licence->shouldReceive('getId')->andReturn(123);

        $companySubsidiary = m::mock(\Dvsa\Olcs\Api\Entity\Organisation\CompanySubsidiary::class);
        $companySubsidiary->shouldReceive('getLicence')->andReturn($licence);

        $mockCsRepo = $this->mockRepo('CompanySubsidiary');
        $mockCsRepo->shouldReceive('fetchByIds')->with([111])->andReturn([$companySubsidiary]);

        $this->setIsValid('canAccessCompanySubsidiary', [111], true);
        $this->setIsValid('canAccessLicence', [123], true);

        $this->assertEquals(true, $this->sut->isValid($dto));
    }

    public function testIsValidWithContextAndOwnershipWithoutMatching(): void
    {
        $data = [
            'ids' => [111],
            'application' => 222
        ];

        $dto = Cmd::create($data);

        $lic = $this->getLicenceFromApplication();
        $lic->shouldReceive('getId')->andReturn(123);

        $licence = m::mock(Licence::class);

        $companySubsidiary = m::mock(\Dvsa\Olcs\Api\Entity\Organisation\CompanySubsidiary::class);
        $companySubsidiary->shouldReceive('getLicence')->andReturn($licence);

        $mockCsRepo = $this->mockRepo('CompanySubsidiary');
        $mockCsRepo->shouldReceive('fetchByIds')->with([111])->andReturn([$companySubsidiary]);

        $this->setIsValid('canAccessCompanySubsidiary', [111], true);
        $this->setIsValid('canAccessLicence', [123], true);

        $this->assertEquals(false, $this->sut->isValid($dto));
    }

    public function getLicenceFromApplication(): mixed
    {
        $licence = m::mock(Licence::class);

        $application = m::mock(Application::class);
        $application->shouldReceive('getLicence')->andReturn($licence);

        $mockApplicationRepo = $this->mockRepo('Application');
        $mockApplicationRepo->shouldReceive('fetchById')->with(222)->andReturn($application);

        return $licence;
    }
}
