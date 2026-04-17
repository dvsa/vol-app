<?php

declare(strict_types=1);

/**
 * Update Inspection Request Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Email\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\Category;
use Mockery as m;
use Dvsa\Olcs\Email\Domain\CommandHandler\UpdateInspectionRequest;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Email\Domain\Command\UpdateInspectionRequest as Cmd;
use Dvsa\Olcs\Api\Domain\Repository;

/**
 * Update Inspection Request Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateInspectionRequestTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var UpdateInspectionRequest
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new UpdateInspectionRequest();

        $this->mockRepo('InspectionRequest', Repository\InspectionRequest::class);

        parent::setUp();
    }

    #[\Override]
    public function initReferences(): void
    {
        $this->refData = [
            InspectionRequest::RESULT_TYPE_NEW,
            InspectionRequest::RESULT_TYPE_SATISFACTORY,
            InspectionRequest::RESULT_TYPE_UNSATISFACTORY
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('successProvider')]
    public function testHandleCommandSuccess(mixed $id, mixed $status, mixed $expectedResultType, mixed $expectedTaskDescription): void
    {
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(7);

        $application = m::mock(Application::class)->makePartial();
        $application->setId(8);

        /** @var InspectionRequest $inspectionRequest */
        $inspectionRequest = m::mock(InspectionRequest::class)->makePartial();
        $inspectionRequest->setId($id);
        $inspectionRequest->setResultType($this->refData[InspectionRequest::RESULT_TYPE_NEW]);
        $inspectionRequest->setLicence($licence);
        $inspectionRequest->setApplication($application);

        $this->repoMap['InspectionRequest']->shouldReceive('fetchById')
            ->once()
            ->with($id)
            ->andReturn($inspectionRequest);

        $this->repoMap['InspectionRequest']->shouldReceive('save')
            ->once()
            ->with($inspectionRequest)
            ->andReturnUsing(
                function (InspectionRequest $inspectionRequest) use ($expectedResultType) {
                    $this->assertSame($this->refData[$expectedResultType], $inspectionRequest->getResultType());
                }
            );

        $expectedTaskData = [
            'category'       => Category::CATEGORY_LICENSING,
            'subCategory'    => Category::TASK_SUB_CATEGORY_INSPECTION_REQUEST_SEMINAR,
            'description'    => $expectedTaskDescription,
            'isClosed'       => 'N',
            'urgent'         => 'N',
            'licence' => 7,
            'application' => 8
        ];
        $result = new Result();

        $this->expectedSideEffect(CreateTask::class, $expectedTaskData, $result);

        $params = [
            'id' => $id,
            'status' => $status,
        ];

        $response = $this->sut->handleCommand(Cmd::create($params));

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $response->toArray());
    }

    public static function successProvider(): array
    {
        return [
            'satisfactory' => [
                123,
                'S',
                InspectionRequest::RESULT_TYPE_SATISFACTORY,
                'Satisfactory inspection request: ID 123',
            ],
            'unsatisfactory' => [
                123,
                'U',
                InspectionRequest::RESULT_TYPE_UNSATISFACTORY,
                'Unsatisfactory inspection request: ID 123',
            ],
        ];
    }

    public function testHandleCommandNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $id = 123;
        $status = 'S';

        $this->repoMap['InspectionRequest']->shouldReceive('fetchById')
            ->once()
            ->with($id)
            ->andThrow(NotFoundException::class);

        $params = [
            'id' => $id,
            'status' => $status,
        ];
        $response = $this->sut->handleCommand(Cmd::create($params));

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $response->toArray());
    }

    public function testHandleCommandInvalidStatusCode(): void
    {
        $this->expectException(ValidationException::class);

        $id = 123;
        $status = 'foo';

        $params = [
            'id' => $id,
            'status' => $status,
        ];

        $response = $this->sut->handleCommand(Cmd::create($params));

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $response->toArray());
    }

    public function testHandleCommandNoOp(): void
    {
        $id = 123;
        $status = 'S';

        /** @var InspectionRequest $inspectionRequest */
        $inspectionRequest = m::mock(InspectionRequest::class)->makePartial();
        $inspectionRequest->setId($id);
        $inspectionRequest->setResultType($this->refData[InspectionRequest::RESULT_TYPE_SATISFACTORY]);

        $this->repoMap['InspectionRequest']->shouldReceive('fetchById')
            ->once()
            ->with($id)
            ->andReturn($inspectionRequest);

        $params = [
            'id' => $id,
            'status' => $status,
        ];

        $response = $this->sut->handleCommand(Cmd::create($params));

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $response->toArray());
    }
}
