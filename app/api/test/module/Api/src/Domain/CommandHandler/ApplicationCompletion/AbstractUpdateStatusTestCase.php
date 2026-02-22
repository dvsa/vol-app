<?php

declare(strict_types=1);

/**
 * Abstract Update Status Test Case
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Abstract Update Status Test Case
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractUpdateStatusTestCase extends AbstractCommandHandlerTestCase
{
    /**
     * @var ApplicationCompletionEntity
     */
    protected $applicationCompletion;

    /**
     * @var ApplicationEntity
     */
    protected $application;

    /**
     * @var LicenceEntity
     */
    protected $licence;

    protected $command;

    protected $section;

    public function setUp(): void
    {
        $this->mockRepo('Application', Application::class);

        parent::setUp();

        $this->applicationCompletion = m::mock(ApplicationCompletionEntity::class)->makePartial();

        $this->licence = m::mock(LicenceEntity::class)->makePartial();

        $this->application = m::mock(ApplicationEntity::class)->makePartial();
        $this->application->setApplicationCompletion($this->applicationCompletion);
        $this->application->setLicence($this->licence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($this->command, Query::HYDRATE_OBJECT)
            ->andReturn($this->application);
    }

    protected function expectStatusUnchanged(mixed $status): void
    {
        $this->commonExpectations($this->section . ' section status is unchanged');

        $this->assertEquals($status, $this->applicationCompletion->{'get' . $this->section . 'Status'}());
    }

    protected function expectStatusChange(mixed $status): void
    {
        $this->repoMap['Application']->shouldReceive('save')
            ->once()
            ->with($this->application);

        $this->commonExpectations($this->section . ' section status has been updated');

        $this->assertEquals($status, $this->applicationCompletion->{'get' . $this->section . 'Status'}());
    }

    protected function commonExpectations(mixed $message): void
    {
        $result = $this->sut->handleCommand($this->command);

        $expected = [
            'id' => [],
            'messages' => [$message]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
