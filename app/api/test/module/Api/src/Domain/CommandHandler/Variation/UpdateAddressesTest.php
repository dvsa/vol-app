<?php

declare(strict_types=1);

/**
 * Update Addresses test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Variation;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Variation\UpdateAddresses;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCommand;
use Dvsa\Olcs\Api\Domain\Command\Licence\SaveAddresses;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\Variation\UpdateAddresses as Cmd;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Update Addresses test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class UpdateAddressesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateAddresses();
        $this->mockRepo('Application', ApplicationRepo::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleCommandWithDirtyAddresses(): void
    {
        $data = [
            'id' => 123,
            'correspondence' => 'corr',
            'contact' => 'contact',
            'correspondenceAddress' => 'c_address',
            'establishment' => 'est',
            'establishmentAddress' => 'e_address',
            'consultant' => 'consultant'
        ];
        $command = Cmd::create($data);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(true);

        $result = new Result();

        $result->setFlag('isDirty', true);

        // licence ID overrides application ID
        $addressData = array_merge($data, ['id' => 456]);

        $this->expectedSideEffect(
            SaveAddresses::class,
            $addressData,
            $result
        );

        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            [
                'id' => 123,
                'section' => 'addresses'
            ],
            $result
        );

        $now = (new DateTime())->format('Y-m-d H:i:s');
        $this->expectedSideEffect(
            CreateTask::class,
            [
                'category' => Category::CATEGORY_APPLICATION,
                'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_ADDRESS_CHANGE_DIGITAL,
                'description' => 'Address Change',
                'licence' => 456,
                'actionDate' => $now,
                'application' => null,
                'assignedToUser' => null,
                'assignedToTeam' => null,
                'isClosed' => false,
                'urgent' => false,
                'busReg' => null,
                'case' => null,
                'transportManager' => null,
                'irfoOrganisation' => null,
            ],
            $result
        );

        $application = m::mock(ApplicationEntity::class)
            ->makePartial()
            ->shouldReceive('getId')
            ->andReturn(123)
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock(LicenceEntity::class)
                ->shouldReceive('getId')
                ->andReturn(456)
                ->getMock()
            )
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
            ],
            'flags' => ['isDirty' => true, 'hasChanged' => true]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertTrue($result->getFlag('hasChanged'));
    }
}
