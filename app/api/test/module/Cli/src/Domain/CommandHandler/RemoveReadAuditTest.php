<?php

declare(strict_types=1);

/**
 * Remove Read Audit Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler;

use Dvsa\Olcs\Cli\Domain\CommandHandler\RemoveReadAudit;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;

/**
 * Remove Read Audit Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class RemoveReadAuditTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RemoveReadAudit();
        $this->mockRepo('ApplicationReadAudit', Repository\ApplicationReadAudit::class);
        $this->mockRepo('LicenceReadAudit', Repository\LicenceReadAudit::class);
        $this->mockRepo('CasesReadAudit', Repository\CasesReadAudit::class);
        $this->mockRepo('BusRegReadAudit', Repository\BusRegReadAudit::class);
        $this->mockRepo('TransportManagerReadAudit', Repository\TransportManagerReadAudit::class);
        $this->mockRepo('OrganisationReadAudit', Repository\OrganisationReadAudit::class);

        $this->mockedSmServices['config'] = [
            'batch_config' => [
                'remove-read-audit' => [
                    'max-age' => '1 year'
                ]
            ]
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $date = date('Y-m-d', strtotime('-1 year'));

        $this->repoMap['ApplicationReadAudit']->shouldReceive('deleteOlderThan')
            ->once()->with($date)->andReturn(10);

        $this->repoMap['LicenceReadAudit']->shouldReceive('deleteOlderThan')
            ->once()->with($date)->andReturn(10);

        $this->repoMap['CasesReadAudit']->shouldReceive('deleteOlderThan')
            ->once()->with($date)->andReturn(10);

        $this->repoMap['BusRegReadAudit']->shouldReceive('deleteOlderThan')
            ->once()->with($date)->andReturn(10);

        $this->repoMap['OrganisationReadAudit']->shouldReceive('deleteOlderThan')
            ->once()->with($date)->andReturn(10);

        $this->repoMap['TransportManagerReadAudit']->shouldReceive('deleteOlderThan')
            ->once()->with($date)->andReturn(10);

        $response = $this->sut->handleCommand(\Dvsa\Olcs\Cli\Domain\Command\RemoveReadAudit::create([]));

        $expected = [
            'id' => [],
            'messages' => [
                '10 ApplicationReadAudit records older than ' . $date . ' removed',
                '10 LicenceReadAudit records older than ' . $date . ' removed',
                '10 CasesReadAudit records older than ' . $date . ' removed',
                '10 BusRegReadAudit records older than ' . $date . ' removed',
                '10 TransportManagerReadAudit records older than ' . $date . ' removed',
                '10 OrganisationReadAudit records older than ' . $date . ' removed',
            ]
        ];

        $this->assertEquals($expected, $response->toArray());
    }
}
