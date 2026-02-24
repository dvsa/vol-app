<?php

declare(strict_types=1);

/**
 * Delete Condition/Undertakings Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\OperatingCentre;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteConditionUndertakings as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\OperatingCentre\DeleteConditionUndertakings as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Delete Condition/Undertakings Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class DeleteConditionUndertakingsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ConditionUndertaking', Repository\ConditionUndertaking::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [
            OperatingCentre::class => [
                1 => m::mock(OperatingCentre::class),
            ],
            Application::class => [
                11 => m::mock(Application::class)->makePartial(),
            ],
            Licence::class => [
                22 => m::mock(Licence::class)->makePartial(),
            ],
            ConditionUndertaking::class => [
                101 => m::mock(ConditionUndertaking::class)->makePartial(),
                102 => m::mock(ConditionUndertaking::class)->makePartial(),
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandApplicationWrongStatus(): void
    {
        $oc = $this->mapReference(OperatingCentre::class, 1);
        $application = $this->mapReference(Application::class, 11);

        $application->shouldReceive('isUnderConsideration')->andReturn(false);

        $data = [
            'operatingCentre' => $oc,
            'application' => $application,
        ];

        $command = Cmd::create($data);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandApplication(): void
    {
        $oc = $this->mapReference(OperatingCentre::class, 1);
        $application = $this->mapReference(Application::class, 11);

        $application->shouldReceive('isUnderConsideration')->andReturn(true);

        $data = [
            'operatingCentre' => $oc,
            'application' => $application,
        ];

        $criteria = null;
        $conditionUndertakings = [
            $this->mapReference(ConditionUndertaking::class, 101),
            $this->mapReference(ConditionUndertaking::class, 102),
        ];
        $oc->shouldReceive('getConditionUndertakings->matching')
            ->once()
            ->andReturnUsing(
                function ($c) use (&$criteria, $conditionUndertakings) {
                    $criteria = $c;
                    return $conditionUndertakings;
                }
            );

        $this->repoMap['ConditionUndertaking']
            ->shouldReceive('delete')
            ->with($this->mapReference(ConditionUndertaking::class, 101))
            ->once()
            ->shouldReceive('delete')
            ->with($this->mapReference(ConditionUndertaking::class, 102))
            ->once();

        $command = Cmd::create($data);

        $result = $this->sut->handleCommand($command);

        $expr = $criteria->getWhereExpression();
        $this->assertEquals('application', $expr->getField());
        $this->assertEquals($application, $expr->getValue()->getValue());
        $this->assertEquals('=', $expr->getOperator());

        $expected = [
            'id' => [],
            'messages' => [
                '2 Condition/Undertaking(s) removed for Operating Centre 1',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandLicence(): void
    {
        $oc = $this->mapReference(OperatingCentre::class, 1);
        $licence = $this->mapReference(Licence::class, 22);

        $licence->shouldReceive('isUnderConsideration')->andReturn(true);

        $data = [
            'operatingCentre' => $oc,
            'licence' => $licence,
        ];

        $criteria = null;
        $conditionUndertakings = [
            $this->mapReference(ConditionUndertaking::class, 101),
            $this->mapReference(ConditionUndertaking::class, 102),
        ];
        $oc->shouldReceive('getConditionUndertakings->matching')
            ->once()
            ->andReturnUsing(
                function ($c) use (&$criteria, $conditionUndertakings) {
                    $criteria = $c;
                    return $conditionUndertakings;
                }
            );

        $this->repoMap['ConditionUndertaking']
            ->shouldReceive('delete')
            ->with($this->mapReference(ConditionUndertaking::class, 101))
            ->once()
            ->shouldReceive('delete')
            ->with($this->mapReference(ConditionUndertaking::class, 102))
            ->once();

        $command = Cmd::create($data);

        $result = $this->sut->handleCommand($command);

        $expr = $criteria->getWhereExpression();
        $this->assertEquals('licence', $expr->getField());
        $this->assertEquals($licence, $expr->getValue()->getValue());
        $this->assertEquals('=', $expr->getOperator());

        $expected = [
            'id' => [],
            'messages' => [
                '2 Condition/Undertaking(s) removed for Operating Centre 1',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
