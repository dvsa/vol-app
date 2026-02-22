<?php

declare(strict_types=1);

/**
 * DeletePeopleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Repository\Licence as  LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\OrganisationPerson as  OrganisationPersonRepo;
use Dvsa\Olcs\Api\Domain\Repository\Person as  PersonRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\DeletePeople as CommandHandler;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Command\Licence\DeletePeople as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * DeletePeopleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeletePeopleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('OrganisationPerson', OrganisationPersonRepo::class);
        $this->mockRepo('Person', PersonRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'title_mr', 'org_t_llp', 'org_t_p'
        ];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 52,
            'personIds' => [543,63]
        ];
        $command = Command::create($data);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($this->refData['org_t_p']);
        $organisation->setId(48);
        $licence = new LicenceEntity($organisation, new \Dvsa\Olcs\Api\Entity\System\RefData());
        $licence->setId(52);
        $op1 = new \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson();
        $p1 = (new \Dvsa\Olcs\Api\Entity\Person\Person())->setId(185);
        $op1->setId(3)->setPerson($p1);
        $op2 = new \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson();
        $op2->setId(54);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($licence);

        $this->repoMap['OrganisationPerson']->shouldReceive('fetchListForOrganisationAndPerson')
            ->with(48, 543)->once()->andReturn([$op1]);
        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\OrganisationPerson\DeleteList::class,
            ['ids' => [3]],
            (new \Dvsa\Olcs\Api\Domain\Command\Result())->addMessage('3 DELETED')
        );

        $this->repoMap['OrganisationPerson']->shouldReceive('fetchListForOrganisationAndPerson')
            ->with(48, 63)->once()->andReturn([$op2]);
        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\OrganisationPerson\DeleteList::class,
            ['ids' => [54]],
            (new \Dvsa\Olcs\Api\Domain\Command\Result())->addMessage('54 DELETED')
        );

        $this->expectedLicenceCacheClearSideEffect(52);

        $response = $this->sut->handleCommand($command);

        $this->assertSame(
            [
                '3 DELETED',
                '54 DELETED',
            ],
            $response->getMessages()
        );
    }
}
