<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\CommunityLicence;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\CommunityLicence\ReportingBulkReprint as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

/**
 * Bulk Reprint with reporting test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ReportingBulkReprintTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setOptions(
            json_encode(
                [
                    'identifier' => 'documents/Report/Community_licence/2019/02/20190214154659264859__community_licence_bulk_reprint.csv',
                    'user' => 41
                ]
            )
        );

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(
            [
                'documentIdentifier' => 'documents/Report/Community_licence/2019/02/20190214154659264859__community_licence_bulk_reprint.csv',
                'user' => 41
            ],
            $result
        );
    }
}
