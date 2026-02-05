<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\GovUkAccount;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\GovUkAccount\GovUkAccountService;
use Dvsa\Olcs\Api\Service\GovUkAccount\Response\GetAuthorisationUrlResponse;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\GovUkAccount\GetGovUkAccountRedirect as GetGovUkAccountRedirectHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\GovUkAccount\GetGovUkAccountRedirect as GetGovUkAccountRedirectCmd;

/**
 * Get GovUkAccount Redirect Test
 */
class GetGovUkAccountRedirectTest extends AbstractCommandHandlerTestCase
{
    public $acctService = null;

    public function setUp(): void
    {
        $this->acctService = m::mock(GovUkAccountService::class);
        $this->sut = new GetGovUkAccountRedirectHandler($this->acctService);
        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $cmdData = [
            'returnUrl' => 'https://olcs-seldserve/some/path',
            'id' => 1,
            'role' => RefData::TMA_SIGN_AS_TM,
            'journey' => RefData::JOURNEY_CONTINUATION
        ];

        $command = GetGovUkAccountRedirectCmd::create($cmdData);

        $urlResponse = new GetAuthorisationUrlResponse('http://url/', 'state', '123');

        $this->acctService->shouldReceive('createStateToken')
            ->with($command->getArrayCopy())
            ->andReturn('stateToken')
            ->shouldReceive('getAuthorisationUrl')
            ->with('stateToken', true)
            ->andReturn($urlResponse);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => ["http://url/"]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
