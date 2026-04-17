<?php

declare(strict_types=1);

/**
 * RestorePeopleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Repository\Licence as  LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Person as  PersonRepo;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOrganisationPerson as AppOrgPersonRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\RestorePeople as CommandHandler;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\Application\RestorePeople as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * RestorePeopleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class RestorePeopleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', LicenceRepo::class);
        $this->mockRepo('Person', PersonRepo::class);
        $this->mockRepo('ApplicationOrganisationPerson', AppOrgPersonRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'title_mr', 'org_t_llp', 'org_t_st'
        ];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 52,
            'personIds' => [79, 234]
        ];
        $command = Command::create($data);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($this->refData['org_t_st']);
        $organisation->setId('87');
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, new \Dvsa\Olcs\Api\Entity\System\RefData());
        $person = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person->setId(79);
        $person->setForename('FRED');
        $application = new ApplicationEntity($licence, new \Dvsa\Olcs\Api\Entity\System\RefData(), 0);
        $application->setId(52);

        $appOrgPerson1 = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson(
            $application,
            $organisation,
            $person
        );
        $appOrgPerson1->setId(79);
        $appOrgPerson2 = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson(
            $application,
            $organisation,
            new \Dvsa\Olcs\Api\Entity\Person\Person()
        );
        $appOrgPerson2->setId(234);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('fetchForApplicationAndPerson')
            ->with(52, 79)->once()->andReturn($appOrgPerson1);
        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('delete')
            ->with($appOrgPerson1)->once();

        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('fetchForApplicationAndPerson')
            ->with(52, 234)->once()->andThrow(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('fetchForApplicationAndOriginalPerson')
            ->with(52, 234)->once()->andReturn($appOrgPerson2);
        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('delete')
            ->with($appOrgPerson2)->once();
        $this->repoMap['Person']->shouldReceive('delete')
            ->with($appOrgPerson2->getPerson())->once();

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            ['section' => 'people', 'id' => 52],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(
            ['ApplicationOrganisationPerson ID 79 deleted', 'ApplicationOrganisationPerson ID 234 deleted'],
            $response->getMessages()
        );
    }
}
