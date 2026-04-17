<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\CreateTask as Sut;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

class CreateTaskTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Sut::class;

    public function testGetCommandData(): void
    {
        $taskData = [
            'category' => 'some_category',
            'subCategory' => 'some_sub_category',
            'description' => 'some_description',
            'actionDate' => '2020-01-01',
            'licence' => 7,
            'urgent' => 'Y',
            'assignedToTeam' => 'team_1'
        ];

        $item = new QueueEntity();
        $item->setOptions(json_encode($taskData));

        $result = $this->sut->getCommandData($item);

        $this->assertEquals($taskData, $result);
    }
}
