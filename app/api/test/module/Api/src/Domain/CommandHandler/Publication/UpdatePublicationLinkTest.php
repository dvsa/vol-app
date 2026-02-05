<?php

declare(strict_types=1);

/**
 * Update Publication Link Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Publication;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Publication\UpdatePublicationLink;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink as PublicationLinkRepo;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Transfer\Command\Publication\UpdatePublicationLink as UpdatePublicationLinkCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Update Publication Link Test
 */
class UpdatePublicationLinkTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdatePublicationLink();
        $this->mockRepo('PublicationLink', PublicationLinkRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $text1 = 'text1';
        $text2 = 'text2';
        $text3 = 'text3';
        $id = 7;
        $version = 9;

        $data = [
            'id' => $id,
            'text1' => $text1,
            'text2' => $text2,
            'text3' => $text3,
            'version' => $version
        ];

        $command = UpdatePublicationLinkCmd::create($data);

        $publication = m::mock(PublicationEntity::class);
        $publication->shouldReceive('canGenerate')->andReturn(true);

        /** @var PublicationLinkEntity $publicationLink */
        $publicationLink = new PublicationLinkEntity();
        $publicationLink->setId($id);
        $publicationLink->setPublication($publication);

        $this->repoMap['PublicationLink']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->andReturn($publicationLink);

        $this->repoMap['PublicationLink']->shouldReceive('save')
            ->once()
            ->with(m::type(PublicationLinkEntity::class));

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'PublicationLink' => $id,
            ],
            'messages' => [
                'Publication entry updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
