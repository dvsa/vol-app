<?php

declare(strict_types=1);

/**
 * Update Safety Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateSafety;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\UpdateSafety as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Update Safety Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateSafetyTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateSafety();
        $this->mockRepo('Application', Application::class);
        $this->mockRepo('Licence', Licence::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = ['tach_ext'];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommandWithValidationException(): void
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'safetyConfirmation' => 'N',
            'partial' => false,
            'licence' => [
                'id' => 222,
                'version' => 1,
                'safetyInsVehicles' => 2,
                'safetyInsTrailers' => 3,
                'safetyInsVaries' => 'Y',
                'tachographIns' => 'tach_ext',
                'tachographInsName' => 'Some name'
            ]
        ];
        $command = Cmd::create($data);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    public static function dpHandleCommand(): array
    {
        return [
            [
                'canHaveTrailer' => true,
                'totAuthTrailers' => 12,
                'expectedSafetyInsTrailers' => 3,
            ],
            [
                'canHaveTrailer' => true,
                'totAuthTrailers' => 0,
                'expectedSafetyInsTrailers' => 0,
            ],
            [
                'canHaveTrailer' => false,
                'totAuthTrailers' => 12,
                'expectedSafetyInsTrailers' => null,
            ],
            [
                'canHaveTrailer' => false,
                'totAuthTrailers' => 0,
                'expectedSafetyInsTrailers' => null,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(mixed $canHaveTrailer, mixed $totAuthTrailers, mixed $expectedSafetyInsTrailers): void
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'safetyConfirmation' => 'Y',
            'partial' => false,
            'licence' => [
                'id' => 222,
                'version' => 1,
                'safetyInsVehicles' => 2,
                'safetyInsTrailers' => 3,
                'safetyInsVaries' => 'Y',
                'tachographIns' => 'tach_ext',
                'tachographInsName' => 'Some name'
            ]
        ];
        $command = Cmd::create($data);

        $licence = new LicenceEntity(
            new OrganisationEntity(),
            new RefData()
        );

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);
        $application->setTotAuthTrailers($totAuthTrailers);
        $application->shouldReceive('canHaveTrailer')
            ->withNoArgs()
            ->andReturn($canHaveTrailer);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->shouldReceive('save')
            ->with($application);

        $this->repoMap['Licence']->shouldReceive('save')->with($licence)->once();

        $this->expectedSideEffect(
            UpdateApplicationCompletion::class,
            [
                'id' => 111,
                'section' => 'safety'
            ],
            (new Result())->addMessage('Section updated')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(2, $licence->getSafetyInsVehicles());
        $this->assertSame($expectedSafetyInsTrailers, $licence->getSafetyInsTrailers());
        $this->assertSame($this->refData['tach_ext'], $licence->getTachographIns());
        $this->assertSame('Some name', $licence->getTachographInsName());
        $this->assertSame('Y', $licence->getSafetyInsVaries());

        $expected = [
            'id' => [],
            'messages' => [
                'Application updated',
                'Section updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
