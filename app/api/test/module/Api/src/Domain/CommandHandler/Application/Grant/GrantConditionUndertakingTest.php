<?php

declare(strict_types=1);

/**
 * Grant Condition Undertaking Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application\Grant;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\GrantConditionUndertaking as GrantConditionUndertakingCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant\GrantConditionUndertaking;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * Grant Condition Undertaking Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GrantConditionUndertakingTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GrantConditionUndertaking();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('ConditionUndertaking', \Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking::class);
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ConditionUndertaking::TYPE_CONDITION
        ];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommandWithoutRecords(): void
    {
        $data = [
            'id' => 111
        ];

        $command = GrantConditionUndertakingCmd::create($data);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setConditionUndertakings(new ArrayCollection());

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandAdded(): void
    {
        $data = [
            'id' => 111
        ];

        $command = GrantConditionUndertakingCmd::create($data);

        $user = m::mock(User::class);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        /** @var ConditionUndertaking $cu1 */
        $cu1 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu1->setAction('A');
        $cu1->setIsFulfilled('Y');
        $cu1->setConditionType($this->refData[ConditionUndertaking::TYPE_CONDITION]);
        $cu1->setConditionCategory('CONDITION_CAT');

        $cuRecords = new ArrayCollection();
        $cuRecords->add($cu1);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setConditionUndertakings($cuRecords);
        $application->setLicence($licence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['ConditionUndertaking']->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (ConditionUndertaking $cu) use ($user, $cu1) {
                    $this->assertSame($user, $cu->getApprovalUser());
                    $this->assertSame($cu1->getAddedVia(), $cu->getAddedVia());
                    $this->assertSame($cu1->getAttachedTo(), $cu->getAttachedTo());
                    $this->assertSame($cu1->getCase(), $cu->getCase());
                    $this->assertSame($cu1->getOperatingCentre(), $cu->getOperatingCentre());
                    $this->assertSame($cu1->getNotes(), $cu->getNotes());
                    $this->assertSame($cu1->getConditionCategory(), $cu->getConditionCategory());
                    $this->assertNull($cu->getS4());
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '1 licence condition undertaking record(s) created',
                '0 licence condition undertaking record(s) updated',
                '0 licence condition undertaking record(s) removed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandUpdated(): void
    {
        $data = [
            'id' => 111
        ];

        $command = GrantConditionUndertakingCmd::create($data);

        $user = m::mock(User::class);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        /** @var ConditionUndertaking $lcu1 */
        $lcu1 = m::mock(ConditionUndertaking::class)->makePartial();

        /** @var ConditionUndertaking $cu1 */
        $cu1 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu1->setAction('U');
        $cu1->setIsFulfilled('Y');
        $cu1->setConditionType($this->refData[ConditionUndertaking::TYPE_CONDITION]);
        $cu1->setLicConditionVariation($lcu1);

        $cuRecords = new ArrayCollection();
        $cuRecords->add($cu1);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setConditionUndertakings($cuRecords);
        $application->setLicence($licence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['ConditionUndertaking']->shouldReceive('save')
            ->once()
            ->with($lcu1)
            ->andReturnUsing(
                function (ConditionUndertaking $cu) use ($user, $lcu1) {
                    $this->assertSame($user, $cu->getApprovalUser());
                    $this->assertSame($lcu1, $cu);
                    $this->assertNull($cu->getS4());
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '0 licence condition undertaking record(s) created',
                '1 licence condition undertaking record(s) updated',
                '0 licence condition undertaking record(s) removed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandDeleted(): void
    {
        $data = [
            'id' => 111
        ];

        $command = GrantConditionUndertakingCmd::create($data);

        $user = m::mock(User::class);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        /** @var ConditionUndertaking $lcu1 */
        $lcu1 = m::mock(ConditionUndertaking::class)->makePartial();

        /** @var ConditionUndertaking $cu1 */
        $cu1 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu1->setAction('D');
        $cu1->setIsFulfilled('Y');
        $cu1->setConditionType($this->refData[ConditionUndertaking::TYPE_CONDITION]);
        $cu1->setLicConditionVariation($lcu1);

        $cuRecords = new ArrayCollection();
        $cuRecords->add($cu1);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setConditionUndertakings($cuRecords);
        $application->setLicence($licence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['ConditionUndertaking']->shouldReceive('save')
            ->once()
            ->with($lcu1)
            ->andReturnUsing(
                function (ConditionUndertaking $cu) use ($user, $lcu1) {
                    $this->assertSame($user, $cu->getApprovalUser());
                    $this->assertSame($lcu1, $cu);
                    $this->assertNull($cu->getS4());
                }
            )
            ->shouldReceive('delete')
            ->once()
            ->with($lcu1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '0 licence condition undertaking record(s) created',
                '0 licence condition undertaking record(s) updated',
                '1 licence condition undertaking record(s) removed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
