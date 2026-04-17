<?php

declare(strict_types=1);

/**
 * Create Workshop Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\CreateWorkshop;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\CreateWorkshop as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Command\Workshop\CreateWorkshop as WorkshopCreateWorkshop;

/**
 * Create Workshop Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateWorkshopTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateWorkshop();
        $this->mockRepo('Application', Application::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'application' => 111,
            'isExternal' => 'Y'
        ];

        $command = Cmd::create($data);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(222);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($application);

        $expectedData = [
            'isExternal' => 'Y',
            'licence' => 222,
            'contactDetails' => null
        ];
        $result1 = new Result();
        $result1->addId('workshop', 123);
        $result1->addMessage('Created workshop');

        $this->expectedSideEffect(WorkshopCreateWorkshop::class, $expectedData, $result1);

        $expectedData = [
            'id' => 111,
            'section' => 'safety'
        ];
        $result2 = new Result();
        $result2->addMessage('Section updated');

        $this->expectedSideEffect(UpdateApplicationCompletion::class, $expectedData, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'workshop' => 123
            ],
            'messages' => [
                'Created workshop',
                'Section updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
