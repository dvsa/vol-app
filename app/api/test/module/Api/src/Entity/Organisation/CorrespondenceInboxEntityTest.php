<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Organisation;

use Mockery as m;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox as Entity;

/**
 * CorrespondenceInbox Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class CorrespondenceInboxEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstruct(): void
    {
        $licence = m::mock(Licence::class);
        $document = m::mock(Document::class);

        $record = new CorrespondenceInbox($licence, $document);

        $this->assertSame($licence, $record->getLicence());
        $this->assertSame($document, $record->getDocument());
    }

    public function testGetRelatedOrganisation(): void
    {
        $organisation = m::mock(Organisation::class);
        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getOrganisation')->with()->once()->andReturn($organisation);
        $document = m::mock(Document::class);

        $sut = new CorrespondenceInbox($licence, $document);

        $this->assertSame($organisation, $sut->getRelatedOrganisation());
    }
}
