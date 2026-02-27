<?php

declare(strict_types=1);

/**
 * Post Submission Task Queue Consumer Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Permits;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\PostSubmitTasks as Sut;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

class PostSubmitTasksTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $item = new QueueEntity();
        $item->setEntityId(187);
        $item->setOptions(json_encode(['irhpPermitType' => 1]));

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(
            [
                'id' => 187,
                'irhpPermitType' => 1,
            ],
            $result
        );
    }
}
