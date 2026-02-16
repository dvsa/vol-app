<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\EventHistory;

use Dvsa\Olcs\Api\Domain\Repository\EventHistory as EventHistoryRepo;
use Dvsa\Olcs\Api\Domain\Repository\EventHistoryType as EventHistoryTypeRepo;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistory as EventHistoryEntity;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Service\EventHistory\Creator;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact as PhoneContactEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Licence\Workshop as WorkshopEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as ConditionUndertakingEntity;

/**
 * CreatorTest
 */
class CreatorTest extends MockeryTestCase
{
    public $sut;
    private $authService;

    private $eventHistoryRepo;

    private $eventHistoryTypeRepo;

    private $user;

    public function setUp(): void
    {
        $this->user = m::mock(UserEntity::class);

        $this->authService = m::mock(AuthorizationService::class);
        $this->authService->shouldReceive('getIdentity->getUser')
            ->withNoArgs()
            ->andReturn($this->user);

        $this->eventHistoryRepo = m::mock(EventHistoryRepo::class);

        $this->eventHistoryTypeRepo = m::mock(EventHistoryTypeRepo::class);

        $this->sut = new Creator(
            $this->authService,
            $this->eventHistoryRepo,
            $this->eventHistoryTypeRepo
        );
    }

    public function testCreateForLicence(): void
    {
        $entityId = 100;
        $entityVersion = 1;
        $eventHistoryType = EventHistoryTypeEntity::EVENT_CODE_SURRENDER_UNDER_CONSIDERATION;

        $entity = m::mock(LicenceEntity::class);
        $entity->expects('getId')
            ->withNoArgs()
            ->andReturn($entityId);
        $entity->expects('getVersion')
            ->withNoArgs()
            ->andReturn($entityVersion);

        $eventHistoryTypeEntity = m::mock(EventHistoryTypeEntity::class);

        $this->eventHistoryTypeRepo->expects('fetchOneByEventCode')
            ->with($eventHistoryType)
            ->andReturn($eventHistoryTypeEntity);

        $this->eventHistoryRepo->expects('save')
            ->with(m::type(EventHistoryEntity::class))
            ->andReturnUsing(
                function (EventHistoryEntity $eventHistory) use ($entity, $entityId, $entityVersion) {
                    $this->assertSame($entity, $eventHistory->getLicence());
                    $this->assertSame('licence', $eventHistory->getEntityType());
                    $this->assertSame($entityId, $eventHistory->getEntityPk());
                    $this->assertSame($entityVersion, $eventHistory->getEntityVersion());
                }
            );

        $this->sut->create($entity, $eventHistoryType);
    }

    public function testCreateForIrhpApplication(): void
    {
        $entityId = 100;
        $entityVersion = 1;
        $eventHistoryType = EventHistoryTypeEntity::IRHP_APPLICATION_CREATED;

        $entity = m::mock(IrhpApplicationEntity::class);
        $entity->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($entityId)
            ->shouldReceive('getVersion')
            ->withNoArgs()
            ->andReturn($entityVersion);

        $eventHistoryTypeEntity = m::mock(EventHistoryTypeEntity::class);

        $this->eventHistoryTypeRepo->shouldReceive('fetchOneByEventCode')
            ->with($eventHistoryType)
            ->andReturn($eventHistoryTypeEntity);

        $this->eventHistoryRepo->shouldReceive('save')
            ->with(m::type(EventHistoryEntity::class))
            ->once()
            ->andReturnUsing(
                function (EventHistoryEntity $eventHistory) use ($entity, $entityId, $entityVersion) {
                    $this->assertSame($entity, $eventHistory->getIrhpApplication());
                    $this->assertSame('irhp_application', $eventHistory->getEntityType());
                    $this->assertSame($entityId, $eventHistory->getEntityPk());
                    $this->assertSame($entityVersion, $eventHistory->getEntityVersion());
                }
            );

        $this->sut->create($entity, $eventHistoryType);
    }

    public function testCreateForUpdateUser(): void
    {
        $entityId = 612;
        $entityVersion = 2;
        $eventHistoryType = EventHistoryTypeEntity::USER_EMAIL_ADDRESS_UPDATED;
        $eventData = "New:new Old:old";

        $entity = m::mock(UserEntity::class);
        $entity->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($entityId)
            ->shouldReceive('getVersion')
            ->withNoArgs()
            ->andReturn($entityVersion);

        $eventHistoryTypeEntity = m::mock(EventHistoryTypeEntity::class);

        $this->eventHistoryTypeRepo->shouldReceive('fetchOneByEventCode')
            ->with($eventHistoryType)
            ->andReturn($eventHistoryTypeEntity);

        $this->eventHistoryRepo->shouldReceive('save')
            ->with(m::type(EventHistoryEntity::class))
            ->once()
            ->andReturnUsing(
                function (EventHistoryEntity $eventHistory) use ($entity, $entityId, $entityVersion, $eventData) {
                    $this->assertSame($entity, $eventHistory->getUser());
                    $this->assertSame('user', $eventHistory->getEntityType());
                    $this->assertSame($entityId, $eventHistory->getEntityPk());
                    $this->assertSame($entityVersion, $eventHistory->getEntityVersion());
                    $this->assertSame($eventData, $eventHistory->getEventData());
                }
            );

        $this->sut->create($entity, $eventHistoryType, $eventData);
    }

    public function testCreateForUndefinedEntity(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot create event history for the entity');

        $eventHistoryType = EventHistoryTypeEntity::IRHP_APPLICATION_CREATED;

        $entity = m::mock(EventHistoryEntity::class);

        $eventHistoryTypeEntity = m::mock(EventHistoryTypeEntity::class);

        $this->eventHistoryTypeRepo->shouldReceive('fetchOneByEventCode')
            ->with($eventHistoryType)
            ->andReturn($eventHistoryTypeEntity);

        $this->eventHistoryRepo->shouldReceive('save')
            ->never();

        $this->sut->create($entity, $eventHistoryType);
    }

    public function testCreateForPhoneContact(): void
    {
        $licenceEntity = m::mock(LicenceEntity::class);
        $entity = m::mock(PhoneContactEntity::class);
        $entity->expects('getId')->andReturn(10);
        $entity->expects('getVersion')->andReturn(1);

        $eventHistoryTypeEntity = m::mock(EventHistoryTypeEntity::class);
        $eventHistoryType = EventHistoryTypeEntity::EVENT_CODE_CONDITION_CHANGED;

        $this->eventHistoryTypeRepo->expects('fetchOneByEventCode')
            ->with($eventHistoryType)
            ->andReturn($eventHistoryTypeEntity);

        $this->eventHistoryRepo->expects('save')
            ->with(m::type(EventHistoryEntity::class))
            ->andReturnUsing(function (EventHistoryEntity $eventHistory) use ($licenceEntity) {
                $this->assertSame($licenceEntity, $eventHistory->getLicence());
                $this->assertSame('phone_contact', $eventHistory->getEntityType());
            });

        $this->sut->create($entity, $eventHistoryType, null, $licenceEntity);
    }

    public function testCreateForContactDetails(): void
    {
        $licenceEntity = m::mock(LicenceEntity::class);
        $entity = m::mock(ContactDetailsEntity::class);
        $entity->expects('getId')->andReturn(10);
        $entity->expects('getVersion')->andReturn(1);

        $eventHistoryTypeEntity = m::mock(EventHistoryTypeEntity::class);
        $eventHistoryType = EventHistoryTypeEntity::EVENT_CODE_CHANGE_CORRESPONDENCE_ADDRESS;

        $this->eventHistoryTypeRepo->expects('fetchOneByEventCode')
            ->with($eventHistoryType)
            ->andReturn($eventHistoryTypeEntity);

        $this->eventHistoryRepo->expects('save')
            ->with(m::type(EventHistoryEntity::class))
            ->andReturnUsing(function (EventHistoryEntity $eventHistory) use ($licenceEntity) {
                $this->assertSame($licenceEntity, $eventHistory->getLicence());
                $this->assertSame('contact_details', $eventHistory->getEntityType());
            });

        $this->sut->create($entity, $eventHistoryType, null, $licenceEntity);
    }

    public function testCreateForWorkshop(): void
    {
        $licenceEntity = m::mock(LicenceEntity::class);
        $entity = m::mock(WorkshopEntity::class);
        $entity->expects('getId')->andReturn(123);
        $entity->expects('getVersion')->andReturn(1);
        $entity->expects('getLicence')->andReturn($licenceEntity);

        $eventHistoryTypeEntity = m::mock(EventHistoryTypeEntity::class);
        $eventHistoryType = EventHistoryTypeEntity::EVENT_CODE_ADD_SAFETY_INSPECTOR;

        $this->eventHistoryTypeRepo->expects('fetchOneByEventCode')
            ->with($eventHistoryType)
            ->andReturn($eventHistoryTypeEntity);

        $this->eventHistoryRepo->expects('save')
            ->with(m::type(EventHistoryEntity::class))
            ->andReturnUsing(function (EventHistoryEntity $eventHistory) use ($licenceEntity) {
                $this->assertSame($licenceEntity, $eventHistory->getLicence());
                $this->assertSame('workshop', $eventHistory->getEntityType());
            });

        $this->sut->create($entity, $eventHistoryType);
    }

    public function testCreateForAddress(): void
    {
        $licenceEntity = m::mock(LicenceEntity::class);
        $entity = m::mock(AddressEntity::class);
        $entity->expects('getId')->andReturn(10);
        $entity->expects('getVersion')->andReturn(1);

        $eventHistoryTypeEntity = m::mock(EventHistoryTypeEntity::class);
        $eventHistoryType = EventHistoryTypeEntity::EVENT_CODE_CHANGE_CORRESPONDENCE_ADDRESS;

        $this->eventHistoryTypeRepo->expects('fetchOneByEventCode')
            ->with($eventHistoryType)
            ->andReturn($eventHistoryTypeEntity);

        $this->eventHistoryRepo->expects('save')
            ->with(m::type(EventHistoryEntity::class))
            ->andReturnUsing(function (EventHistoryEntity $eventHistory) use ($licenceEntity) {
                $this->assertSame($licenceEntity, $eventHistory->getLicence());
                $this->assertSame('address', $eventHistory->getEntityType());
            });

        $this->sut->create($entity, $eventHistoryType, null, $licenceEntity);
    }

    public function testCreateForConditionUndertaking(): void
    {
        $case = m::mock(CaseEntity::class);
        $application = m::mock(ApplicationEntity::class);
        $licence = m::mock(LicenceEntity::class);

        $entity = m::mock(ConditionUndertakingEntity::class);
        $entity->expects('getId')->andReturn(101);
        $entity->expects('getVersion')->andReturn(1);
        $entity->expects('getCase')->andReturn($case);
        $entity->expects('getApplication')->andReturn($application);
        $entity->expects('getLicence')->andReturn($licence);

        $eventHistoryTypeEntity = m::mock(EventHistoryTypeEntity::class);
        $eventHistoryType = 'CONDITION_EVENT';

        $this->eventHistoryTypeRepo->expects('fetchOneByEventCode')
            ->with($eventHistoryType)
            ->andReturn($eventHistoryTypeEntity);

        $this->eventHistoryRepo->expects('save')
            ->with(m::type(EventHistoryEntity::class))
            ->andReturnUsing(function (EventHistoryEntity $eventHistory) use ($entity, $case, $application, $licence) {
                $this->assertSame($case, $eventHistory->getCase());
                $this->assertSame($application, $eventHistory->getApplication());
                $this->assertSame($licence, $eventHistory->getLicence());
                $this->assertSame('condition_undertaking', $eventHistory->getEntityType());
            });

        $this->sut->create($entity, $eventHistoryType);
    }
}
