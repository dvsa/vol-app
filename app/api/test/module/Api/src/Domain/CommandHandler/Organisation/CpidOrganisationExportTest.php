<?php

declare(strict_types=1);

/**
 * CpidOrganisationExportTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Organisation;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Organisation\CpidOrganisationExport;
use Dvsa\Olcs\Transfer\Command\Organisation\CpidOrganisationExport as Cmd;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Class CpidOrganisationExportTest
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CpidOrganisationExportTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CpidOrganisationExport();

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'cpid' => null
        ];

        $command = Cmd::create($data);

        $this->expectedSideEffect(
            Create::class,
            [
                'options' => json_encode(['status' => null]),
                'type' => Queue::TYPE_CPID_EXPORT_CSV,
                'status' => Queue::STATUS_QUEUED
            ],
            (new Result())->addMessage('RESULT')
        );

        $this->sut->handleCommand($command);
    }
}
