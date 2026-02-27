<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateBusinessDetails;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * @covers  Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateBusinessDetails
 */
class UpdateBusinessDetailsTest extends AbstractCommandHandlerTestCase
{
    public const ID = 111;
    public const LIC_ID = 2222;

    /** @var UpdateBusinessDetails  */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new UpdateBusinessDetails();

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => self::ID,
            'licence' => self::LIC_ID,
            'version' => 1,
        ];
        $command = TransferCmd\Application\UpdateBusinessDetails::create($data);

        //  save business details
        $saveCmdData = [
            'id' => self::LIC_ID,
            'version' => 1,
            'name' => null,
            'natureOfBusiness' => null,
            'companyOrLlpNo' => null,
            'registeredAddress' => null,
            'tradingNames' => [],
            'partial' => null,
            'allowEmail' => null,
        ];

        $saveCmdResult = new Result();
        $saveCmdResult->addMessage('Business Details updated');
        $saveCmdResult->setFlag('hasChanged', true);

        $this->expectedSideEffect(
            DomainCmd\Licence\SaveBusinessDetails::class,
            $saveCmdData,
            $saveCmdResult
        );

        //  update application completion
        $completionCmdData = [
            'id' => self::ID,
            'section' => 'businessDetails',
            'data' => [
                'hasChanged' => true,
            ]
        ];

        $completionCmdResult = new Result();
        $completionCmdResult->addMessage('Section updated');

        $this->expectedSideEffect(
            DomainCmd\Application\UpdateApplicationCompletion::class,
            $completionCmdData,
            $completionCmdResult
        );

        //  call
        $actual = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Business Details updated',
                'Section updated'
            ],
            'flags' => ['hasChanged' => 1]
        ];

        static::assertEquals($expected, $actual->toArray());
    }
}
