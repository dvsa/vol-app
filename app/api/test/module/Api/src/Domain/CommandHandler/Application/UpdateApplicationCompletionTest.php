<?php

declare(strict_types=1);

/**
 * Update Application Completion Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateAddressesStatus;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateBusinessTypeStatus;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateTypeOfLicenceStatus;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateVariationCompletion as UpdateVariationCompletionCommand;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\Permission;

/**
 * Update Application Completion Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateApplicationCompletionTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateApplicationCompletion();
        $this->mockRepo('Application', Application::class);
        $this->mockRepo('DigitalSignature', \Dvsa\Olcs\Api\Domain\Repository\AbstractRepository::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class)->makePartial();

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['id' => 111, 'section' => 'typeOfLicence']);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false);

        /** @var ApplicationCompletion $applicationCompletion */
        $applicationCompletion = m::mock(ApplicationCompletion::class)->makePartial();

        // Should update these
        $applicationCompletion->setAddressesStatus(ApplicationCompletion::STATUS_COMPLETE);
        $applicationCompletion->setBusinessTypeStatus(ApplicationCompletion::STATUS_INCOMPLETE);
        $applicationCompletion->setTypeOfLicenceStatus(ApplicationCompletion::STATUS_NOT_STARTED);
        // Shouldn't update these
        $applicationCompletion->setBusinessDetailsStatus(ApplicationCompletion::STATUS_NOT_STARTED);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setApplicationCompletion($applicationCompletion);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($application);

        $result1 = new Result();
        $result1->addMessage('Tol updated');
        $this->expectedSideEffect(UpdateTypeOfLicenceStatus::class, ['id' => 111], $result1);

        $result2 = new Result();
        $result2->addMessage('Addresses updated');
        $this->expectedSideEffect(UpdateAddressesStatus::class, ['id' => 111], $result2);

        $result3 = new Result();
        $result3->addMessage('Bt updated');
        $this->expectedSideEffect(UpdateBusinessTypeStatus::class, ['id' => 111], $result3);

        $result = $this->sut->handleCommand($command);

        $messages = $result->toArray()['messages'];

        // Order of the array can't be guaranteed, so here we assert that the messages are present
        $this->assertCount(3, $messages);
        $this->assertTrue(in_array('Addresses updated', $messages));
        $this->assertTrue(in_array('Bt updated', $messages));
        $this->assertTrue(in_array('Tol updated', $messages));
    }

    public function testHandleCommandIsVariation(): void
    {
        $command = Cmd::create(['id' => 111, 'section' => 'section1']);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setIsVariation(true);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($application);

        $this->expectedSideEffect(
            UpdateVariationCompletionCommand::class,
            ['id' => 111, 'section' => 'section1'],
            new Result()
        );

        $this->sut->handleCommand($command);
    }

    /**
     *
     * @param $expectResetSignature
     * @param $section
     * @param $isSelfserve
     * @param $applicationStatus
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommandResetSignature')]
    public function testHandleCommandResetSignature(mixed $expectResetSignature, mixed $section, mixed $isSelfserve, mixed $applicationStatus): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn($isSelfserve);

        $command = Cmd::create(['id' => 111, 'section' => $section]);

        /** @var ApplicationCompletion $applicationCompletion */
        $applicationCompletion = m::mock(ApplicationCompletion::class)->makePartial();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setStatus(new \Dvsa\Olcs\Api\Entity\System\RefData($applicationStatus));
        $application->setApplicationCompletion($applicationCompletion);

        $digitalSignature = new \Dvsa\Olcs\Api\Entity\DigitalSignature();
        $application->setDigitalSignature($digitalSignature);
        $application->setDeclarationConfirmation('Y');
        $application->setSignatureType('FOO');

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($application);

        $result1 = new Result();
        $result1->addMessage('Tol updated');
        $this->expectedSideEffect(
            'Dvsa\\Olcs\\Api\\Domain\\Command\\ApplicationCompletion\\Update' . ucfirst((string) $section) . 'Status',
            ['id' => 111],
            $result1
        );

        if ($expectResetSignature) {
            $this->repoMap['DigitalSignature']->shouldReceive('delete')->with($digitalSignature)->once();
        }

        $this->sut->handleCommand($command);

        if ($expectResetSignature) {
            $this->assertSame('N', $application->getDeclarationConfirmation());
            $this->assertSame(null, $application->getSignatureType());
            $this->assertSame(null, $application->getDigitalSignature());
        } else {
            $this->assertSame('Y', $application->getDeclarationConfirmation());
            $this->assertSame($digitalSignature, $application->getDigitalSignature());
            $this->assertSame('FOO', $application->getSignatureType());
        }
    }

    public static function dpTestHandleCommandResetSignature(): array
    {
        return [
            [true, 'typeOfLicence', true, ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED],
            [false, 'typeOfLicence', true, ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION],
            [false, 'typeOfLicence', true, ApplicationEntity::APPLICATION_STATUS_GRANTED],
            [false, 'typeOfLicence', true, ApplicationEntity::APPLICATION_STATUS_VALID],

            [true, 'addresses', true, ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED],
            [true, 'typeOfLicence', true, ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED],
            [true, 'financialHistory', true, ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED],
            [false, 'undertakings', true, ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED],
            [false, 'declarationsInternal', true, ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED],

            [false, 'addresses', false, ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED],
            [false, 'typeOfLicence', false, ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED],
            [false, 'financialHistory', false, ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED],
            [false, 'undertakings', false, ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED],
            [false, 'declarationsInternal', false, ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED],
        ];
    }

    public function testHandleCommandResetSignatureNoExisting(): void
    {
        $section = 'typeOfLicence';
        $applicationStatus = ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED;

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(true);
        $command = Cmd::create(['id' => 111, 'section' => $section]);

        /** @var ApplicationCompletion $applicationCompletion */
        $applicationCompletion = m::mock(ApplicationCompletion::class)->makePartial();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setStatus(new \Dvsa\Olcs\Api\Entity\System\RefData($applicationStatus));
        $application->setApplicationCompletion($applicationCompletion);
        $application->setDeclarationConfirmation('Y');
        $application->setSignatureType('FOO');

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($application);

        $result1 = new Result();
        $result1->addMessage('Tol updated');
        $this->expectedSideEffect(
            'Dvsa\\Olcs\\Api\\Domain\\Command\\ApplicationCompletion\\Update' . ucfirst($section) . 'Status',
            ['id' => 111],
            $result1
        );

        $this->sut->handleCommand($command);

        $this->assertSame('N', $application->getDeclarationConfirmation());
        $this->assertSame(null, $application->getSignatureType());
        $this->assertSame(null, $application->getDigitalSignature());
    }
}
