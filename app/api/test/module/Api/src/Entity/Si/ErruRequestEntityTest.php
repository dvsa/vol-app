<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Si;

use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as Entity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * ErruRequest Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class ErruRequestEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Tests creation of erru requests
     */
    public function testCreate(): void
    {
        $case = m::mock(CaseEntity::class);
        $msiType = m::mock(RefData::class);
        $memberStateCode = m::mock(CountryEntity::class);
        $requestDocument = m::mock(Document::class);
        $originatingAuthority = 'originating authority';
        $transportUndertakingName = 'transport undertaking';
        $vrm = 'vrm';
        $notificationNumber = '0ffefb6b-6344-4a60-9a53-4381c32f98d9';
        $workflowId = '20776dc3-5fe7-42d5-b554-09ad12fa25c4';
        $communityLicenceStatus = m::mock(RefData::class);
        $communityLicenceNumber = 'UKGB/OB1234567/00000';
        $totAuthVehicles = 10;

        $entity = new Entity(
            $case,
            $msiType,
            $memberStateCode,
            $requestDocument,
            $communityLicenceStatus,
            $communityLicenceNumber,
            $totAuthVehicles,
            $originatingAuthority,
            $transportUndertakingName,
            $vrm,
            $notificationNumber,
            $workflowId
        );

        $this->assertEquals($case, $entity->getCase());
        $this->assertEquals($msiType, $entity->getMsiType());
        $this->assertEquals($memberStateCode, $entity->getMemberStateCode());
        $this->assertEquals($requestDocument, $entity->getRequestDocument());
        $this->assertEquals($originatingAuthority, $entity->getOriginatingAuthority());
        $this->assertEquals($transportUndertakingName, $entity->getTransportUndertakingName());
        $this->assertEquals($vrm, $entity->getVrm());
        $this->assertEquals($notificationNumber, $entity->getNotificationNumber());
        $this->assertEquals($workflowId, $entity->getWorkflowId());
        $this->assertEquals($totAuthVehicles, $entity->getTotAuthVehicles());
        $this->assertEquals($communityLicenceStatus, $entity->getCommunityLicenceStatus());
        $this->assertEquals($communityLicenceNumber, $entity->getCommunityLicenceNumber());
    }

    public function testQueueErruResponse(): void
    {
        $user = m::mock(UserEntity::class);
        $date = new \DateTime();
        $document = m::mock(Document::class);

        /** @var Entity $entity */
        $entity = m::mock(Entity::class)->makePartial();

        $entity->queueErruResponse($user, $date, $document);

        $this->assertEquals($user, $entity->getResponseUser());
        $this->assertEquals($date, $entity->getResponseTime());
        $this->assertEquals($document, $entity->getResponseDocument());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('canModifyProvider')]
    public function testCanModify(string $msiStatus, bool $isNew): void
    {
        $msiType = m::mock(RefData::class);
        $msiType->shouldReceive('getId')->once()->andReturn($msiStatus);

        $entity = $this->instantiate(Entity::class);
        $entity->setMsiType($msiType);

        $this->assertEquals($isNew, $entity->canModify());
    }

    /**
     * @return array
     */
    public static function canModifyProvider(): array
    {
        return [
            [Entity::FAILED_CASE_TYPE, false],
            [Entity::SENT_CASE_TYPE, false],
            [Entity::DEFAULT_CASE_TYPE, true]
        ];
    }
}
