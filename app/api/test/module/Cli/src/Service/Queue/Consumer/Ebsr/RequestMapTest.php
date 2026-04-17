<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessRequestMap as ProcessRequestMapCmd;
use Dvsa\Olcs\Api\Domain\Command\Queue\Failed as FailedCmd;
use Dvsa\Olcs\Api\Domain\Command\Queue\Retry as RetryCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Domain\Exception\TransxchangeException;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Ebsr\RequestMap;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Cli\Service\Queue\Consumer\Ebsr\RequestMap::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer::class)]
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class RequestMapTest extends AbstractConsumerTestCase
{
    protected $consumerClass = RequestMap::class;

    /** @var  RequestMap */
    protected $sut;

    public function testGetCommandData(): void
    {
        $user = new User('pid', 'type');
        $user->setId(1);
        $item = new QueueEntity();
        $item->setOptions(json_encode(['foo' => 'bar']));
        $item->setCreatedBy($user);

        $result = $this->sut->getCommandData($item);

        $this->assertEquals(['foo' => 'bar', 'user' => 1], $result);
    }

    /**
     * Tests task is created when map request fails
     */
    public function testFailed(): void
    {
        $busRegId = 123;
        $regNo = '456/789';
        $licence = 101112;
        $userId = 131415;

        $user = new User('pid', 'type');
        $user->setId($userId);

        $options = [
            'id' => $busRegId,
            'regNo' => $regNo,
            'licence' => $licence,
            'user' => $userId
        ];

        $item = new QueueEntity();
        $item->setId($busRegId);
        $item->setOptions(json_encode($options));
        $item->setCreatedBy($user);

        $taskData = [
            'category' => TaskEntity::CATEGORY_BUS,
            'subCategory' => TaskEntity::SUBCATEGORY_EBSR,
            'description' => sprintf(RequestMap::TASK_FAIL_DESC, $regNo),
            'actionDate' => date('Y-m-d'),
            'busReg' => $busRegId,
            'licence' => $licence,
        ];

        $cmd = CreateTaskCmd::create($taskData);

        $this->expectCommand(
            FailedCmd::class,
            [
                'item' => $item,
                'lastError' => 'unit_LastErr',
            ],
            new Result(),
            false
        );
        $this->expectCommand(CreateTaskCmd::class, $cmd->getArrayCopy(), new Result());

        $this->sut->failed($item, 'unit_LastErr');
    }
}
