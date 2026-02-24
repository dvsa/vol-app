<?php

declare(strict_types=1);

/**
 * Send Temporary Password Email Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendUserTemporaryPassword as Sut;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUserTemporaryPassword as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Send Temporary Password Email Test
 */
class SendUserTemporaryPasswordTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('User', UserRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandDataProvider')]
    public function testHandleCommand(mixed $isInternal, mixed $expectedUrl): void
    {
        $userId = 111;
        $emailAddress = 'me@test.me';

        $command = Cmd::create(
            [
                'user' => $userId,
                'password' => 'GENERATED_PASSWORD'
            ]
        );

        /** @var ContactDetails $contactDetails */
        $contactDetails = m::mock(ContactDetails::class)->makePartial();
        $contactDetails->setEmailAddress($emailAddress);

        /** @var User $user */
        $user = m::mock(User::class)->makePartial();
        $user->setId($userId);
        $user->setContactDetails($contactDetails);
        $user->shouldReceive('isInternal')->andReturn($isInternal);

        $this->repoMap['User']->shouldReceive('fetchById')
            ->with($userId)
            ->andReturn($user);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')
            ->once()
            ->with(
                m::type(Message::class),
                'user-temporary-password',
                [
                    'password' => 'GENERATED_PASSWORD',
                    'url' => $expectedUrl
                ],
                'default'
            );

        $this->expectedSideEffect(
            SendEmail::class,
            [
                'to' => $emailAddress
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => $userId
            ],
            'messages' => [
                'User temporary password email sent'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function handleCommandDataProvider(): array
    {
        return [
            [false, 'http://selfserve/'],
            [true, 'http://internal/'],
        ];
    }
}
