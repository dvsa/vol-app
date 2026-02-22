<?php

declare(strict_types=1);

/**
 * Tm Nominated Task Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Licence\TmNominatedTask as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\TmNominatedTask;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * Tm Nominated Task Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TmNominatedTaskTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new TmNominatedTask();
        $this->mockRepo('Licence', Repository\Licence::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => [
                111,
                222
            ]
        ];

        $command = Cmd::create($data);

        /** @var TransportManagerLicence $tmLicence1 */
        $tmLicence1 = m::mock(TransportManagerLicence::class);

        $tmLicences1 = new ArrayCollection();
        $tmLicences1->add($tmLicence1);

        // Empty
        $tmLicences2 = new ArrayCollection();

        /** @var Licence $licence1 */
        $licence1 = m::mock(Licence::class)->makePartial();
        $licence1->setId(111);
        $licence1->setTmLicences($tmLicences1);

        /** @var Licence $licence2 */
        $licence2 = m::mock(Licence::class)->makePartial();
        $licence2->setId(222);
        $licence2->setTmLicences($tmLicences2);

        $licences = [
            $licence1,
            $licence2
        ];

        $this->repoMap['Licence']->shouldReceive('fetchByIds')
            ->with([111, 222])
            ->andReturn($licences);

        /** @var Team $team */
        $team = m::mock(Team::class)->makePartial();
        $team->setId(22);

        /** @var User $user */
        $user = m::mock(User::class)->makePartial();
        $user->setId(11);
        $user->setTeam($team);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        $result1 = new Result();
        $data = [
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::TASK_SUB_CATEGORY_TM_PERIOD_OF_GRACE,
            'description' => 'Transport manager to be nominated',
            'actionDate' => (new DateTime('+14 days'))->format('Y-m-d'),
            'assignedToUser' => 11,
            'assignedToTeam' => 22,
            'licence' => 222
        ];
        $this->expectedSideEffect(CreateTask::class, $data, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '1 tm nominated task(s) created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
