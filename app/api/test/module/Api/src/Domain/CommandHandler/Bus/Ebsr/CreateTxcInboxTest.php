<?php

declare(strict_types=1);

/**
 * Create TxcInbox Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr\CreateTxcInbox;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\CreateTxcInbox as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Create TxcInbox Test
 */
class CreateTxcInboxTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateTxcInbox();
        $this->mockRepo('Bus', BusRepo::class);

        parent::setUp();
    }

    /**
     * Tests handleCommand
     */
    public function testHandleCommand(): void
    {
        $busRegId = 11;

        $organisationName = 'organisation name';
        $organisation = new OrganisationEntity();
        $organisation->setName($organisationName);

        $zipDocument = m::mock(DocumentEntity::class);
        $ebsrSubmission = m::mock(EbsrSubmissionEntity::class);
        $ebsrSubmission->shouldReceive('getOrganisation')->once()->andReturn($organisation);
        $ebsrSubmission->shouldReceive('getDocument')->once()->andReturn($zipDocument);
        $ebsrSubmissions = new ArrayCollection([$ebsrSubmission]);

        $localAuthorityDescription = 'local authority description';
        $localAuthority = new LocalAuthorityEntity();
        $localAuthority->setDescription($localAuthorityDescription);
        $localAuthorities = new ArrayCollection([$localAuthority]);

        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getEbsrSubmissions')->once()->andReturn($ebsrSubmissions);
        $busReg->shouldReceive('getLocalAuthoritys')->once()->andReturn($localAuthorities);
        $busReg->shouldReceive('isFromEbsr')->twice()->andReturn(true);
        $busReg->shouldReceive('getVariationNo')->twice()->andReturn(123456);
        $busReg->shouldReceive('setTxcInboxs')->once()->with(m::type(ArrayCollection::class));

        $command = Cmd::create(['id' => $busRegId]);

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->once()
            ->andReturn($busReg);

        $this->repoMap['Bus']->shouldReceive('save')
            ->once()
            ->with(m::type(BusRegEntity::class));

        $result = $this->sut->handleCommand($command);

        $expected = [
            'Txc Inbox record created for ' . $localAuthorityDescription,
            'Txc Inbox record created for ' . $organisationName
        ];

        $this->assertEquals($expected, $result->getMessages());
    }
}
