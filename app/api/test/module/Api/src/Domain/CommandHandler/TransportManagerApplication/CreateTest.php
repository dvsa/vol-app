<?php

declare(strict_types=1);

/**
 * CreateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication\Create as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\TransportManager;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication;
use Dvsa\Olcs\Api\Domain\Repository\User;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\Create as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication as TransportManagerApplicationEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\Queue\Queue;

/**
 * CreateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    protected $loggedInUser;

    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('TransportManagerApplication', TransportManagerApplication::class);
        $this->mockRepo('TransportManager', TransportManager::class);
        $this->mockRepo('User', User::class);

        $this->mockedSmServices = [
            \LmcRbacMvc\Service\AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class)
        ];

        $this->loggedInUser = m::mock(UserEntity::class)->makePartial();
        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')->andReturn($this->loggedInUser);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = ['tmap_st_incomplete', 'tm_s_cur'];

        $this->references = [
            \Dvsa\Olcs\Api\Entity\Application\Application::class => [
                863 => m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommandAlreadyTmLoggedInUser(): void
    {
        $command = Command::create(['application' => 863, 'user' => 234, 'action' => 'D']);

        $user = $this->loggedInUser;
        $mockTm = m::mock()
            ->shouldReceive('getId')
            ->andReturn(21)
            ->getMock();

        $user->setTransportManager($mockTm);

        $this->repoMap['User']->shouldReceive('fetchForTma')->with(234)->once()->andReturn($user);
        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication $tma) {
                $tma->setId(534);

                $this->assertSame('D', $tma->getAction());
                $this->assertSame(
                    $this->references[\Dvsa\Olcs\Api\Entity\Application\Application::class][863],
                    $tma->getApplication()
                );
                $this->assertSame($this->refData['tmap_st_incomplete'], $tma->getTmApplicationStatus());
                $this->assertSame(21, $tma->getTransportManager()->getId());
            }
        )
        ->shouldReceive('fetchByTmAndApplication')
        ->with(21, 863, true)
        ->once()
        ->andReturn(null);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            [
                'id' => 863,
                'section' => 'transportManagers'
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandSetDob(): void
    {
        $command = Command::create(['application' => 863, 'user' => 234, 'action' => 'D', 'dob' => '2015-11-26']);

        $user = $this->loggedInUser;
        $this->loggedInUser->shouldReceive('getContactDetails->getPerson->setBirthDate')
            ->andReturnUsing(
                function ($date) {
                    $this->assertEquals(new \Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime('2015-11-26'), $date);
                }
            )
            ->once();

        $mockTm = m::mock()
            ->shouldReceive('getId')
            ->andReturn(21)
            ->getMock();

        $user->setTransportManager($mockTm);

        $this->repoMap['User']->shouldReceive('fetchForTma')->with(234)->once()->andReturn($user);
        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once()
            ->shouldReceive('fetchByTmAndApplication')
            ->with(21, 863, true)
            ->once()
            ->andReturn(null);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            [
                'id' => 863,
                'section' => 'transportManagers'
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandCreateTm(): void
    {
        $command = Command::create(['application' => 863, 'user' => 234, 'action' => 'D']);

        $user = m::mock(UserEntity::class)->makePartial();
        $mockCd = m::mock(ContactDetails::class);
        $mockCd->shouldReceive('getEmailAddress')->andReturn('test@test.com');
        $user->setContactDetails($mockCd);

        $this->repoMap['User']->shouldReceive('fetchForTma')->with(234)->once()->andReturn($user);

        $savedTm = null;
        $this->repoMap['TransportManager']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Tm\TransportManager $tm) use (&$savedTm, $mockCd) {
                $tm->setId(645);

                $this->assertSame($mockCd, $tm->getHomeCd());

                $this->assertSame($this->refData['tm_s_cur'], $tm->getTmStatus());

                $savedTm = $tm;
            }
        );
        $this->repoMap['User']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\User\User $u) use (&$savedTm) {
                $this->assertSame($savedTm, $u->getTransportManager());
            }
        );

        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication $tma) use (&$savedTm) {
                $tma->setId(534);

                $this->assertSame('D', $tma->getAction());
                $this->assertSame(
                    $this->references[\Dvsa\Olcs\Api\Entity\Application\Application::class][863],
                    $tma->getApplication()
                );
                $this->assertSame($this->refData['tmap_st_incomplete'], $tma->getTmApplicationStatus());
                $this->assertSame($savedTm, $tma->getTransportManager());
            }
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\TransportManagerApplication\SendTmApplication::class,
            [
                'id' => 534,
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            [
                'id' => 863,
                'section' => 'transportManagers'
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $this->expectedQueueSideEffect(645, Queue::TYPE_UPDATE_NYSIIS_TM_NAME, ['id' => 645]);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithException(): void
    {
        $this->expectException(
            ValidationException::class
        );
        $command = Command::create(['application' => 863, 'user' => 234, 'action' => 'D']);

        $mockContactDetails = m::mock()
            ->shouldReceive('getPerson')
            ->andReturn(
                m::mock()
                ->shouldReceive('getForename')
                ->andReturn('forename')
                ->once()
                ->shouldReceive('getFamilyname')
                ->andReturn('familyName')
                ->once()
                ->getMock()
            )
            ->getMock();

        $user = m::mock(UserEntity::class)->makePartial();
        $user->setContactDetails($mockContactDetails);
        $mockTm = m::mock()
            ->shouldReceive('getId')
            ->andReturn(21)
            ->getMock();

        $user->setTransportManager($mockTm);

        $this->repoMap['User']->shouldReceive('fetchForTma')->with(234)->once()->andReturn($user);

        $this->repoMap['TransportManagerApplication']
            ->shouldReceive('fetchByTmAndApplication')
            ->with(21, 863, true)
            ->once()
            ->andReturn(['foo']);

        $this->sut->handleCommand($command);
    }
}
