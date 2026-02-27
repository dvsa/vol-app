<?php

declare(strict_types=1);

/**
 * Update MyAccount Selfserve Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\MyAccount;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\MyAccount\UpdateMyAccountSelfserve as Sut;
use Dvsa\Olcs\Transfer\Command\MyAccount\UpdateMyAccountSelfserve as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Update MyAccount Selfserve Test
 */
class UpdateMyAccountSelfserveTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'contactDetails' => [
                'emailAddress' => 'test1@test.me',
                'forename' => 'updated forename',
                'person' => [
                    'familyName' => 'updated familyName',
                ],
            ],
        ];

        $command = Cmd::create($data);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\MyAccount\UpdateMyAccount::class,
            $data,
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }
}
