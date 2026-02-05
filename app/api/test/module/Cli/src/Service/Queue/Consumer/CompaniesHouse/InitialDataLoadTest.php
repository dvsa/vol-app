<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\CompaniesHouse\InitialDataLoad;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;
use Olcs\Logging\Log\Logger;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Cli\Service\Queue\Consumer\CompaniesHouse\InitialDataLoad::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer::class)]
class InitialDataLoadTest extends AbstractConsumerTestCase
{
    protected $consumerClass = InitialDataLoad::class;

    /** @var InitialDataLoad */
    protected $sut;

    public function setUp(): void
    {
        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        parent::setUp();
    }

    public function testProcessMessageSuccess(): void
    {
        $item = new QueueEntity();
        $item->setId(99);
        $item->setOptions('{"companyNumber":"01234567"}');

        $expectedDtoData = ['companyNumber' => '01234567'];
        $cmdResult = new Result();
        $cmdResult
            ->addId('companiesHouseCompany', 101)
            ->addMessage('Company added');

        $this->expectCommand(
            \Dvsa\Olcs\Api\Domain\Command\CompaniesHouse\InitialLoad::class,
            $expectedDtoData,
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
            'Successfully processed message: 99 {"companyNumber":"01234567"} Company added',
            $result
        );
    }

    public function testProcessMessageFailure(): void
    {
        $item = new QueueEntity();
        $item->setId(99);
        $item->setOptions('{"companyNumber":"01234567"}');

        $this->expectCommandException(
            \Dvsa\Olcs\Api\Domain\Command\CompaniesHouse\InitialLoad::class,
            ['companyNumber' => '01234567'],
            \Dvsa\Olcs\Api\Domain\Exception\Exception::class,
            'epic fail'
        );

        $this->expectCommand(
            \Dvsa\Olcs\Api\Domain\Command\Queue\Failed::class,
            [
                'item' => $item,
                'lastError' => 'epic fail',
            ],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            'Failed to process message: 99 {"companyNumber":"01234567"} epic fail',
            $result
        );
    }
}
