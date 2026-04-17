<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Cpms;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Cpms\ReportDownload;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Cli\Service\Queue\Consumer\Cpms\ReportDownload::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer::class)]
class ReportDownloadTest extends AbstractConsumerTestCase
{
    /** @var ReportDownload */
    protected $sut;

    /** @var m\MockInterface */
    protected $qhm;

    #[\Override]
    protected function instantiate(): void
    {
        $this->qhm = m::mock(QueryHandlerManager::class);

        $this->sut = new ReportDownload(
            $this->abstractConsumerServices,
            $this->qhm
        );
    }

    public function testProcessMessageSuccess(): void
    {
        $user = new User('pid', 'type');
        $user->setId(1);
        $item = new QueueEntity();
        $item->setId(99);
        $item->setAttempts(1);
        $item->setOptions('{"reference":"OLCS-1234-ABCD", "name": "FILENAME"}');
        $item->setCreatedBy($user);

        $expectedQryData = ['reference' => 'OLCS-1234-ABCD'];
        $qryResult = [
            'completed' => true,
            'token' => 'secrettoken',
            'extension' => 'csv',
        ];
        $this->expectQuery(
            \Dvsa\Olcs\Transfer\Query\Cpms\ReportStatus::class,
            $expectedQryData,
            $qryResult
        );

        $expectedCmdData = [
            'reference' => 'OLCS-1234-ABCD',
            'token'     => 'secrettoken',
            'filename'  => 'FILENAME.csv',
            'user'      => 1
        ];
        $cmdResult = new Result();
        $cmdResult
            ->addId(
                'identifier',
                "documents/Licensing/Financial_reports/2015/10/20151007110722__Daily_Balance_Report.csv"
            )
            ->addId('document', 666)
            ->addMessage('Report downloaded')
            ->addMessage('File uploaded')
            ->addMessage('Document created');

        $this->expectCommand(
            \Dvsa\Olcs\Transfer\Command\Cpms\DownloadReport::class,
            $expectedCmdData,
            $cmdResult
        );

        $this->expectCommand(
            \Dvsa\Olcs\Api\Domain\Command\Queue\Complete::class,
            ['item' => $item],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            'Successfully processed message: 99 {"reference":"OLCS-1234-ABCD", "name": "FILENAME"} Download using '
            . 'reference OLCS-1234-ABCD and token secrettoken and extension csv|Report downloaded|File '
            . 'uploaded|Document created',
            $result
        );
    }

    public function testProcessMessageMaxAttemptsExceeded(): void
    {
        $user = new User('pid', 'type');
        $user->setId(1);
        $item = new QueueEntity();
        $item->setId(99);
        $item->setAttempts(11);
        $item->setOptions('{"reference":"OLCS-1234-ABCD"}');
        $item->setCreatedBy($user);

        $this->expectCommand(
            \Dvsa\Olcs\Api\Domain\Command\Queue\Failed::class,
            [
                'item' => $item,
                'lastError' =>  QueueEntity::ERR_MAX_ATTEMPTS,
            ],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            'Failed to process message: 99 {"reference":"OLCS-1234-ABCD"} ' . QueueEntity::ERR_MAX_ATTEMPTS,
            $result
        );
    }

    public function testProcessMessageReportNotReady(): void
    {
        $user = new User('pid', 'type');
        $user->setId(1);
        $item = new QueueEntity();
        $item->setId(99);
        $item->setOptions('{"reference":"OLCS-1234-ABCD"}');
        $item->setCreatedBy($user);

        $expectedQryData = ['reference' => 'OLCS-1234-ABCD'];
        $exceptionMessage = 'try again later';
        $expectedException = new \Dvsa\Olcs\Api\Domain\Exception\NotReadyException($exceptionMessage);
        $expectedException->setRetryAfter(60);
        $this->expectQueryException(
            \Dvsa\Olcs\Transfer\Query\Cpms\ReportStatus::class,
            $expectedQryData,
            $expectedException
        );

        $this->expectCommand(
            \Dvsa\Olcs\Api\Domain\Command\Queue\Retry::class,
            ['item' => $item, 'retryAfter' => 60, 'lastError' => $exceptionMessage],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            'Requeued message: 99 {"reference":"OLCS-1234-ABCD"} for retry in 60 ' . $exceptionMessage,
            $result
        );
    }

    public function testProcessMessageFailureFromStatusCheck(): void
    {
        $user = new User('pid', 'type');
        $user->setId(1);
        $item = new QueueEntity();
        $item->setId(99);
        $item->setOptions('{"reference":"OLCS-1234-ABCD"}');
        $item->setCreatedBy($user);

        $expectedQryData = ['reference' => 'OLCS-1234-ABCD'];
        $this->expectQueryException(
            \Dvsa\Olcs\Transfer\Query\Cpms\ReportStatus::class,
            $expectedQryData,
            \Exception::class,
            'unknown fail'
        );

        $this->expectCommand(
            \Dvsa\Olcs\Api\Domain\Command\Queue\Failed::class,
            [
                'item' => $item,
                'lastError' => 'unknown fail',
            ],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            'Failed to process message: 99 {"reference":"OLCS-1234-ABCD"} unknown fail',
            $result
        );
    }

    public function testProcessMessageFailureFromDownload(): void
    {
        $user = new User('pid', 'type');
        $user->setId(1);
        $item = new QueueEntity();
        $item->setId(99);
        $item->setOptions('{"reference":"OLCS-1234-ABCD", "name": "FILENAME"}');
        $item->setCreatedBy($user);

        $expectedQryData = ['reference' => 'OLCS-1234-ABCD'];
        $qryResult = [
            'completed' => true,
            'token' => 'secrettoken',
            'extension' => 'csv',
        ];
        $this->expectQuery(
            \Dvsa\Olcs\Transfer\Query\Cpms\ReportStatus::class,
            $expectedQryData,
            $qryResult
        );

        $expectedCmdData = [
            'reference' => 'OLCS-1234-ABCD',
            'token'     => 'secrettoken',
            'filename'  => 'FILENAME.csv',
            'user'      => 1
        ];
        $this->expectCommandException(
            \Dvsa\Olcs\Transfer\Command\Cpms\DownloadReport::class,
            $expectedCmdData,
            \Dvsa\Olcs\Api\Domain\Exception\Exception::class,
            'backend fail'
        );

        $this->expectCommand(
            \Dvsa\Olcs\Api\Domain\Command\Queue\Failed::class,
            [
                'item' => $item,
                'lastError' => 'backend fail',
            ],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            'Failed to process message: 99 {"reference":"OLCS-1234-ABCD", "name": "FILENAME"} backend fail',
            $result
        );
    }

    /**
     * @param string $class expected Query class name
     * @param array $expectedDtoData
     * @param array $result to be returned by $response->getResult()
     */
    protected function expectQuery(mixed $class, mixed $expectedDtoData, mixed $result): void
    {
        $this->qhm
            ->shouldReceive('handleQuery')
            ->with(
                m::on(
                    function (QueryInterface $qry) use ($expectedDtoData, $class) {
                        $matched = (
                            is_a($qry, $class)
                            &&
                            $qry->getArrayCopy() == $expectedDtoData
                        );
                        return $matched;
                    }
                )
            )
            ->once()
            ->andReturn($result);
    }

    /**
     * @param string $class
     * @param array $expectedDtoData
     * @param string|\Exception $exception
     */
    protected function expectQueryException(mixed $class, mixed $expectedDtoData, mixed $exception, string $exceptionMsg = ''): void
    {
        if (is_string($exception)) {
            $exception = new $exception($exceptionMsg);
        }

        $this->qhm
            ->shouldReceive('handleQuery')
            ->with(
                m::on(
                    function (QueryInterface $qry) use ($expectedDtoData, $class) {
                        $matched = (
                            is_a($qry, $class)
                            &&
                            $qry->getArrayCopy() == $expectedDtoData
                        );
                        return $matched;
                    }
                )
            )
            ->once()
            ->andThrow($exception);
    }
}
