<?php

declare(strict_types=1);

/**
 * Update Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\CompanySubsidiary\Licence;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\CompanySubsidiary\Licence\Update;
use Laminas\ServiceManager\ServiceManager;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateCompanySubsidiary as Cmd;

/**
 * Update Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateTest extends AbstractHandlerTestCase
{
    /**
     * @var Update
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Update();

        parent::setUp();
    }

    public function testIsValidNoContext(): void
    {
        $data = [
            'licence' => null
        ];

        $dto = Cmd::create($data);

        $this->assertFalse($this->sut->isValid($dto));
    }

    public function testIsValidWithContextNoAccess(): void
    {
        $data = [
            'id' => 111,
            'licence' => 222
        ];

        $licence = $this->getLicenceFromLicence();
        $licence->shouldReceive('getId')->andReturn(222);

        $dto = Cmd::create($data);

        $this->setIsValid('canAccessLicence', [222], false);

        $this->assertEquals(false, $this->sut->isValid($dto));
    }

    public function testIsValidWithContextNoOwnership(): void
    {
        $data = [
            'id' => 111,
            'licence' => 222
        ];

        $licence = $this->getLicenceFromLicence();
        $licence->shouldReceive('getId')->andReturn(222);

        $dto = Cmd::create($data);

        $this->setIsValid('canAccessCompanySubsidiary', [111], false);
        $this->setIsValid('canAccessLicence', [222], true);

        $this->assertEquals(false, $this->sut->isValid($dto));
    }

    public function testIsValidWithContextAndOwnership(): void
    {
        $data = [
            'id' => 111,
            'licence' => 222
        ];

        $dto = Cmd::create($data);

        $licence = $this->getLicenceFromLicence();
        $licence->shouldReceive('getId')->andReturn(222);

        $companySubsidiary = m::mock(\Dvsa\Olcs\Api\Entity\Organisation\CompanySubsidiary::class);
        $companySubsidiary->shouldReceive('getLicence')->andReturn($licence);

        $mockCsRepo = $this->mockRepo('CompanySubsidiary');
        $mockCsRepo->shouldReceive('fetchByIds')->with([111])->andReturn([$companySubsidiary]);

        $this->setIsValid('canAccessCompanySubsidiary', [111], true);
        $this->setIsValid('canAccessLicence', [222], true);

        $this->assertEquals(true, $this->sut->isValid($dto));
    }

    public function testIsValidWithContextAndOwnershipWithoutMatching(): void
    {
        $data = [
            'id' => 111,
            'licence' => 222
        ];

        $dto = Cmd::create($data);

        $lic = $this->getLicenceFromLicence();
        $lic->shouldReceive('getId')->andReturn(222);

        $licence = m::mock(Licence::class);

        $companySubsidiary = m::mock(\Dvsa\Olcs\Api\Entity\Organisation\CompanySubsidiary::class);
        $companySubsidiary->shouldReceive('getLicence')->andReturn($licence);

        $mockCsRepo = $this->mockRepo('CompanySubsidiary');
        $mockCsRepo->shouldReceive('fetchByIds')->with([111])->andReturn([$companySubsidiary]);

        $this->setIsValid('canAccessCompanySubsidiary', [111], true);
        $this->setIsValid('canAccessLicence', [222], true);

        $this->assertEquals(false, $this->sut->isValid($dto));
    }

    public function getLicenceFromLicence(): mixed
    {
        $licence = m::mock(Licence::class);

        $mockLicenceRepo = $this->mockRepo('Licence');
        $mockLicenceRepo->shouldReceive('fetchById')->with(222)->andReturn($licence);

        return $licence;
    }
}
