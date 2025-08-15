<?php

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\PrintJob;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\PrintJob as PrintJobCmd;
use Dvsa\Olcs\Api\Domain\Command\Queue\Retry as RetryCmd;
use Dvsa\Olcs\Api\Domain\Exception\NotReadyException;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * Print job test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PrintJobTest extends AbstractConsumerTestCase
{
    protected $consumerClass = \Dvsa\Olcs\Cli\Service\Queue\Consumer\PrintJob\PrintJob::class;

    /** @var  \Dvsa\Olcs\Cli\Service\Queue\Consumer\PrintJob\PrintJob */
    protected $sut;

    /**
    * @dataProvider dpGetCommandData
    */
    public function testGetCommandData($itemId, $entityId, $options, $expected)
    {
        $item = new QueueEntity();
        $item->setId($itemId);
        $item->setEntityId($entityId);
        $item->setOptions(json_encode($options));

        $this->assertEquals($expected, $this->sut->getCommandData($item));
    }

    public function dpGetCommandData()
    {
        return [
            'with list of documents' => [
                'itemId' => 1,
                'entityId' => null,
                'options' => ['jobName' => 'JOB_NAME', 'documents' => [101, 102], 'userId' => 200, 'copies' => 5],
                'expected' => [
                    'id' => 1,
                    'title' => 'JOB_NAME',
                    'documents' => [101, 102],
                    'user' => 200,
                    'copies' => 5,
                ],
            ],
            'with one document' => [
                'itemId' => 1,
                'entityId' => null,
                'options' => ['jobName' => 'JOB_NAME', 'documents' => [101]],
                'expected' => [
                    'id' => 1,
                    'title' => 'JOB_NAME',
                    'documents' => [101],
                    'user' => null,
                    'copies' => null,
                ],
            ],
            'with one document - backward compatibility' => [
                'itemId' => 1,
                'entityId' => 101,
                'options' => ['jobName' => 'JOB_NAME'],
                'expected' => [
                    'id' => 1,
                    'title' => 'JOB_NAME',
                    'documents' => [101],
                    'user' => null,
                    'copies' => null,
                ],
            ],
        ];
    }

    /**
     * Tests that print job retries correctly
     */
    public function testProcessMessageHandlesNotReadyException()
    {
        $message = 'exception message';
        $userId = 11;
        $user = new User('pid', 'type');
        $user->setId($userId);

        $itemId = 99;
        $entityId = 88;
        $jobName = 'job name';

        $optionsArray = [
            'jobName' => $jobName,
            'userId' => $userId,
        ];

        $options = json_encode($optionsArray);

        $item = new QueueEntity();
        $item->setId($itemId);
        $item->setEntityId($entityId);
        $item->setOptions($options);
        $item->setCreatedBy($user);

        $retryAfter = 900;

        $cmdData = [
            'id' => $itemId,
            'title' => $jobName,
            'documents' => [$entityId],
            'user' => $userId,
            'copies' => null,
        ];

        $this->expectCommandException(PrintJobCmd::class, $cmdData, NotReadyException::class, $message, $retryAfter);

        $this->expectCommand(
            RetryCmd::class,
            [
                'item' => $item,
                'retryAfter' => $retryAfter,
                'lastError' => $message,
            ],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            'Requeued message: 99 ' . $options . ' for retry in ' . $retryAfter . ' ' . $message,
            $result
        );
    }
}
