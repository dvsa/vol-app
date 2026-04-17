<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendFailedOrganisationsList as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendFailedOrganisationsList;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

class SendFailedOrganisationsListTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new SendFailedOrganisationsList();
        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $emailAddress = 'toemail@example.com';
        $command = Cmd::create([
            'organisationNumbers' => ['0000', '1111', '2222', '3333'],
            'emailAddress' => $emailAddress,
            'emailSubject' => 'Test email'
        ]);

        $result = new Result();
        $result->addMessage('Email sent');
        $this->expectedSideEffect(
            SendEmail::class,
            [
                'to' => 'toemail@example.com',
                'subject' => 'Test email',
                'plainBody' => "0000\n1111\n2222\n3333"
            ],
            $result
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Email sent'], $result->getMessages());
    }
}
