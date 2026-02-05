<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Publication;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Publication\Publication as Entity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\Doc\DocTemplate as DocTemplateEntity;
use Mockery as m;

/**
 * Publication Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class PublicationEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Test creating a publication
     */
    public function testCreate(): void
    {
        $trafficArea = m::mock(TrafficAreaEntity::class);
        $pubStatus = m::mock(RefData::class);
        $docTemplate = m::mock(DocTemplateEntity::class);
        $pubDate = new \DateTime('2015-12-25 00:00:00'); //14 days later
        $pubType = 'A&D';
        $publicationNo = 111;

        $entity = new Entity($trafficArea, $pubStatus, $docTemplate, $pubDate, $pubType, $publicationNo);

        $this->assertEquals($trafficArea, $entity->getTrafficArea());
        $this->assertEquals($pubStatus, $entity->getPubStatus());
        $this->assertEquals($docTemplate, $entity->getDocTemplate());
        $this->assertEquals($pubDate, $entity->getPubDate());
        $this->assertEquals($pubType, $entity->getPubType());
        $this->assertEquals($publicationNo, $entity->getPublicationNo());
    }

    /**
     * Test updating published documents. Checks police document has been set, and that main document has been set as
     * read only
     */
    public function testUpdatePublishedDocuments(): void
    {
        $policeDocument = m::mock(DocumentEntity::class);

        $entity = $this->instantiate(Entity::class);
        $entity->updatePublishedDocuments($policeDocument);

        $this->assertEquals($policeDocument, $entity->getPoliceDocument());
    }

    /**
     * Tests getting the next publication date
     */
    public function testGetNextPublicationDate(): void
    {
        $pubDate = '2015-12-11';
        $newPubDate  = '2015-12-18'; // +7 days

        $entity = $this->instantiate(Entity::class);
        $entity->setPubDate($pubDate);

        $this->assertEquals($entity->getNextPublicationDate()->format('Y-m-d'), $newPubDate);
    }

    /**
     * Check exception is thrown if no pub date is set when generating the future date
     */
    public function testGetNextPublicationDateThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);

        $entity = $this->instantiate(Entity::class);
        $entity->getNextPublicationDate();
    }

    /**
     * Tests publish
     */
    public function testPublish(): void
    {
        $entity = $this->instantiate(Entity::class);
        $entity->setPubStatus(new RefData(Entity::PUB_GENERATED_STATUS));
        $printedStatus = new RefData(Entity::PUB_PRINTED_STATUS);

        $entity->publish($printedStatus);
        $this->assertEquals($entity::PUB_PRINTED_STATUS, $entity->getPubStatus()->getId());
    }

    /**
     * @param string $pubStatus
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('notGeneratedStatusProvider')]
    public function testPublishThrowsException(mixed $pubStatus): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $entity = $this->instantiate(Entity::class);
        $entity->setPubStatus(new RefData($pubStatus));
        $printedStatus = new RefData(Entity::PUB_PRINTED_STATUS);

        $entity->publish($printedStatus);
    }

    /**
     * Provider of publication statuses that aren't generated
     *
     * @return array
     */
    public static function notGeneratedStatusProvider(): array
    {
        return [
            [Entity::PUB_NEW_STATUS],
            [Entity::PUB_PRINTED_STATUS]
        ];
    }

    /**
     * Tests generate
     */
    public function testGenerate(): void
    {
        $entity = $this->instantiate(Entity::class);
        $entity->setPubStatus(new RefData(Entity::PUB_NEW_STATUS));
        $generateStatus = new RefData(Entity::PUB_GENERATED_STATUS);
        $document = new DocumentEntity(1);

        $entity->generate($document, $generateStatus);
        $this->assertEquals($entity::PUB_GENERATED_STATUS, $entity->getPubStatus()->getId());
    }

    /**
     * @param string $pubStatus
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('notNewStatusProvider')]
    public function testGenerateThrowsException(mixed $pubStatus): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $entity = $this->instantiate(Entity::class);
        $entity->setPubStatus(new RefData($pubStatus));
        $generateStatus = new RefData(Entity::PUB_GENERATED_STATUS);
        $document = new DocumentEntity(1);

        $entity->generate($document, $generateStatus);
    }

    /**
     * Provider of publication statuses that aren't new
     *
     * @return array
     */
    public static function notNewStatusProvider(): array
    {
        return [
            [Entity::PUB_GENERATED_STATUS],
            [Entity::PUB_PRINTED_STATUS]
        ];
    }

    /**
     * Test that isNew function only returns new if the status is new
     *
     * @param $pubStatus
     * @param $isNew
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isNewPublicationStatusProvider')]
    public function testIsNew(mixed $pubStatus, mixed $isNew): void
    {
        $entity = $this->instantiate(Entity::class);
        $entity->setPubStatus(new RefData($pubStatus));

        $this->assertEquals($isNew, $entity->isNew());
    }

    /**
     * Provides statuses and whether isNew should return true or false
     *
     * @return array
     */
    public static function isNewPublicationStatusProvider(): array
    {
        return [
            [Entity::PUB_NEW_STATUS, true],
            [Entity::PUB_GENERATED_STATUS, false],
            [Entity::PUB_PRINTED_STATUS, false]
        ];
    }
}
