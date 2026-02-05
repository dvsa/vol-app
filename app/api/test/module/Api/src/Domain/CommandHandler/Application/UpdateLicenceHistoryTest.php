<?php

declare(strict_types=1);

/**
 * Update Licence History Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateLicenceHistory;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\UpdateLicenceHistory as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;

/**
 * Update Licence History Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UpdateLicenceHistoryTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateLicenceHistory();
        $this->mockRepo('Application', Application::class);

        parent::setUp();
    }

    public function testHandleCommandNotInProgressNoErrors(): void
    {
        $command = $this->getCommand();

        $otherLicences = $this->getOtherLicences();

        $application = $this->getApplication()
            ->shouldReceive('getOtherLicences')
            ->andReturn($otherLicences)
            ->shouldReceive('updateLicenceHistory')
            ->with('Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y')
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->once()
            ->shouldReceive('save')
            ->with($application)
            ->once()
            ->getMock();

        $data = [
            'id' => 1,
            'section' => 'licenceHistory'
        ];
        $result = new Result();
        $result->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Licence history section has been updated',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandNotInProgressWithErrors(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $command = $this->getCommand();

        $otherLicences = $this->getOtherLicences(true);

        $application = $this->getApplication()
            ->shouldReceive('getOtherLicences')
            ->andReturn($otherLicences)
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->once()
            ->getMock();

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandNotInProgressWithEmptyAnswer(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $command = $this->getCommand(false, true);

        $otherLicences = $this->getOtherLicences();

        $application = $this->getApplication()
            ->shouldReceive('getOtherLicences')
            ->andReturn($otherLicences)
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->once()
            ->getMock();

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandInProgress(): void
    {
        $command = $this->getCommand(true);

        $application = $this->getApplication()
            ->shouldReceive('updateLicenceHistory')
            ->with('Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y')
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->once()
            ->shouldReceive('save')
            ->with($application)
            ->once()
            ->getMock();

        $data = [
            'id' => 1,
            'section' => 'licenceHistory'
        ];
        $result = new Result();
        $result->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Licence history section has been updated',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    protected function getCommand(bool $inProgress = false, bool $removeField = false): mixed
    {
        $data = [
            'id' => 1,
            'version' => 1,
            'prevHasLicence' => 'Y',
            'prevHadLicence' => 'Y',
            'prevBeenRefused' => 'Y',
            'prevBeenRevoked' => 'Y',
            'prevBeenAtPi' => 'Y',
            'prevBeenDisqualifiedTc' => 'Y',
            'prevPurchasedAssets' => 'Y',
            'inProgress' => $inProgress
        ];
        if ($removeField) {
            unset($data['prevPurchasedAssets']);
        }

        return Cmd::create($data);
    }

    protected function getApplication(): m\MockInterface
    {
        return m::mock(ApplicationEntity::class)->makePartial();
    }

    protected function getOtherLicences(bool $hideOne = false): mixed
    {
        $otherLicenceCurrent = m::mock()
            ->shouldReceive('getPreviousLicenceType')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(OtherLicence::TYPE_CURRENT)
                ->getMock()
            )
            ->getMock();

        $otherLicenceApplied = m::mock()
            ->shouldReceive('getPreviousLicenceType')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(OtherLicence::TYPE_APPLIED)
                ->getMock()
            )
            ->getMock();

        $otherLicenceRefused = m::mock()
            ->shouldReceive('getPreviousLicenceType')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(OtherLicence::TYPE_REFUSED)
                ->getMock()
            )
            ->getMock();

        $otherLicenceRevoked = m::mock()
            ->shouldReceive('getPreviousLicenceType')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(OtherLicence::TYPE_REVOKED)
                ->getMock()
            )
            ->getMock();

        $otherLicencePi = m::mock()
            ->shouldReceive('getPreviousLicenceType')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(OtherLicence::TYPE_PUBLIC_INQUIRY)
                ->getMock()
            )
            ->getMock();

        $otherLicenceDisqualified = m::mock()
            ->shouldReceive('getPreviousLicenceType')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(OtherLicence::TYPE_DISQUALIFIED)
                ->getMock()
            )
            ->getMock();

        $otherLicenceHeld = m::mock()
            ->shouldReceive('getPreviousLicenceType')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(OtherLicence::TYPE_HELD)
                ->getMock()
            )
            ->getMock();

        $otherLicences = [
            $otherLicenceCurrent, $otherLicenceApplied, $otherLicenceRefused, $otherLicenceRevoked,
            $otherLicencePi, $otherLicenceDisqualified
        ];
        if (!$hideOne) {
            $otherLicences[] = $otherLicenceHeld;
        }
        return $otherLicences;
    }
}
