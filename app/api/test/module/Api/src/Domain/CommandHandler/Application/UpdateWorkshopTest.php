<?php

declare(strict_types=1);

/**
 * Update Workshop Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateWorkshop;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\UpdateWorkshop as Cmd;
use Dvsa\Olcs\Transfer\Command\Workshop\UpdateWorkshop as WorkshopUpdateWorkshop;

/**
 * Update Workshop Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateWorkshopTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateWorkshop();
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
            'id' => 123,
            'application' => 111,
            'isExternal' => 'Y',
            'version' => 2
        ];

        $command = Cmd::create($data);

        $expectedData = [
            'id' => 123,
            'isExternal' => 'Y',
            'contactDetails' => null,
            'version' => 2
        ];
        $result1 = new Result();
        $result1->addMessage('Updated workshop');
        $result1->setFlag('hasChanged', true);

        $this->expectedSideEffect(WorkshopUpdateWorkshop::class, $expectedData, $result1);

        $expectedData = [
            'id' => 111,
            'section' => 'safety',
            'data' => [
                'hasChanged' => true
            ]
        ];
        $result2 = new Result();
        $result2->addMessage('Section updated');

        $this->expectedSideEffect(UpdateApplicationCompletion::class, $expectedData, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Updated workshop',
                'Section updated'
            ],
            'flags' => [
                'hasChanged' => 1
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }
}
