<?php

declare(strict_types=1);

/**
 * Change Business Type Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Organisation;

use Dvsa\Olcs\Api\Domain\CommandHandler\Organisation\ChangeBusinessType;
use Dvsa\Olcs\Api\Domain\Exception\RequiresConfirmationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\CompanySubsidiary;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Organisation\ChangeBusinessType as Cmd;

/**
 * Change Business Type Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ChangeBusinessTypeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ChangeBusinessType();
        $this->mockRepo('Organisation', Repository\Organisation::class);
        $this->mockRepo('CompanySubsidiary', Repository\CompanySubsidiary::class);
        $this->mockRepo('OrganisationPerson', Repository\OrganisationPerson::class);

        parent::setUp();
    }

    #[\Override]
    public function initReferences(): void
    {
        $this->refData = [
            Organisation::ORG_TYPE_SOLE_TRADER
        ];

        parent::initReferences();
    }

    public function testHandleCommandRequiresConfirmation(): void
    {
        $this->expectException(RequiresConfirmationException::class);

        $data = [
            'id' => 111,
            'businessType' => Organisation::ORG_TYPE_SOLE_TRADER
        ];

        $command = Cmd::create($data);

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getType->getId')
            ->andReturn(Organisation::ORG_TYPE_REGISTERED_COMPANY);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($organisation);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111,
            'businessType' => Organisation::ORG_TYPE_SOLE_TRADER,
            'confirm' => true
        ];

        $command = Cmd::create($data);

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getType->getId')
            ->andReturn(Organisation::ORG_TYPE_REGISTERED_COMPANY);

        $organisation->shouldReceive('setCompanyOrLlpNo')->once()->with(null);
        $organisation->shouldReceive('setContactDetails')->once()->with(null);
        $organisation->shouldReceive('setType')->once()
            ->with($this->refData[Organisation::ORG_TYPE_SOLE_TRADER]);

        $sub = m::mock(CompanySubsidiary::class);

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getCompanySubsidiaries')->andReturn([$sub]);

        $person1 = m::mock(OrganisationPerson::class);
        $person2 = m::mock(OrganisationPerson::class);

        $organisation->shouldReceive('getLicences')->andReturn([$licence]);
        $organisation->shouldReceive('getOrganisationPersons')->andReturn([$person1, $person2]);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($organisation)
            ->shouldReceive('save')
            ->once()
            ->with($organisation);

        $this->repoMap['CompanySubsidiary']->shouldReceive('delete')->once()->with($sub);
        $this->repoMap['OrganisationPerson']->shouldReceive('delete')->once()->with($person2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
