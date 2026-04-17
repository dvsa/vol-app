<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\BulkSend;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\BulkSend\Letter as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

/**
 * Bulk Letter Send test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class LetterTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setOptions(
            json_encode(
                [
                    'identifier' => 'documents/Report/bulk_send/licences.csv',
                    'user' => 41,
                    'templateSlug' => 'some-doc-slug'
                ]
            )
        );

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(
            [
                'documentIdentifier' => 'documents/Report/bulk_send/licences.csv',
                'user' => 41,
                'templateSlug' => 'some-doc-slug'
            ],
            $result
        );
    }
}
