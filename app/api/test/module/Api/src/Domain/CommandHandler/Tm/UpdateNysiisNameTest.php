<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\CommandHandler\Tm\UpdateNysiisName;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\TransportManager as TransportManagerRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Tm\UpdateNysiisName as Cmd;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Service\Nysiis\NysiisRestClient;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Transport Manager / Update NYSIIS Name
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Shaun Lizzio <shaun@lizzzio.co.uk>
 */
class UpdateNysiisNameTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateNysiisName();

        $this->mockRepo('TransportManager', TransportManagerRepo::class);

        $this->mockedSmServices[NysiisRestClient::class] = m::mock(NysiisRestClient::class)->makePartial();
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleCommand()
    {
        $this->markTestSkipped('Nysiis service is currently switched off');
        $id = 1;
        $data = [
            'id' => $id
        ];

        $personForename = 'person forename';
        $personFamilyName = 'person family name';

        $nysiisForename = 'nysiis forename';
        $nysiisFamilyName = 'nysiis family name';

        $command = Cmd::create($data);

        $person = m::mock(PersonEntity::class);
        $person->shouldReceive('getForename')->once()->andReturn($personForename);
        $person->shouldReceive('getFamilyName')->once()->andReturn($personFamilyName);

        $transportManager = m::mock(TransportManagerEntity::class);
        $transportManager->shouldReceive('getHomeCd->getPerson')->once()->andReturn($person);
        $transportManager->shouldReceive('setNysiisForename')->once()->andReturn($nysiisForename);
        $transportManager->shouldReceive('setNysiisFamilyName')->once()->andReturn($nysiisFamilyName);

        $nysiisResult = [
            'nysiisFirstName' => $nysiisForename,
            'nysiisFamilyName' => $nysiisFamilyName
        ];

        $this->mockedSmServices[NysiisRestClient::class]
            ->shouldReceive('makeRequest')
            ->once()
            ->with($personForename, $personFamilyName)
            ->andReturn($nysiisResult);

        $this->repoMap['TransportManager']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($transportManager)
            ->once()
            ->shouldReceive('save')
            ->with($transportManager)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals('TM NYSIIS name was requested and updated', $result->getMessages()[0]);
    }
}
