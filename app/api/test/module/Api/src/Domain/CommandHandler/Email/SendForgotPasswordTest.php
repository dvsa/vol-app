<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendForgotPassword;
use Dvsa\Olcs\Api\Domain\Command\Email\SendForgotPassword as SendForgotPasswordCmd;
use Dvsa\Olcs\Api\Domain\Repository\UserPasswordReset as UserPasswordResetRepo;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\User\UserPasswordReset as UserPasswordResetEntity;
use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Laminas\I18n\Translator\LoaderPluginManager;
use Mockery as m;

class SendForgotPasswordTest extends AbstractCommandHandlerTestCase
{
    public $translationLoader;
    public function setUp(): void
    {
        $this->sut = new SendForgotPassword();
        $this->mockRepo('UserPasswordReset', UserPasswordResetRepo::class);

        $this->translationLoader = m::mock(TranslationLoader::class);

        $pluginManager = m::mock(LoaderPluginManager::class);
        $pluginManager->expects('get')->with(TranslationLoader::class)->andReturn($this->translationLoader);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
            'TranslatorPluginManager' => $pluginManager,
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $userId = 111;
        $resetId = 222;
        $emailAddress = 'test@test.com';
        $loginId = 'username';
        $realm = 'selfserve';
        $confirmation = 'confirmation';
        $translateToWelsh = 'N';

        $command = SendForgotPasswordCmd::create(
            [
                'id' => $resetId,
                'realm' => $realm,
            ]
        );

        $callChargeUrl = 'call charge url';
        $licencingPhone = '03950385';
        $licencingEmail = 'licencing-email';

        $replacements = [
            SendForgotPassword::CALL_CHARGE_REPLACEMENT => $callChargeUrl,
            SendForgotPassword::OPERATOR_LICENSING_PHONE_REPLACEMENT => $licencingPhone,
            SendForgotPassword::OPERATOR_LICENSING_EMAIL_REPLACEMENT => $licencingEmail,
        ];

        $resetUrl = sprintf(SendForgotPassword::RESET_URL, $realm, $confirmation, $loginId);

        $this->translationLoader->expects('loadReplacements')->withNoArgs()->andReturn($replacements);

        $user = m::mock(UserEntity::class);
        $user->expects('getId')->withNoArgs()->andReturn($userId);
        $user->expects('getContactDetails->getEmailAddress')->andReturn($emailAddress);
        $user->expects('getLoginId')->andReturn($loginId);
        $user->expects('getTranslateToWelsh')->andReturn($translateToWelsh);

        $userPasswordReset = m::mock(UserPasswordResetEntity::class);
        $userPasswordReset->expects('getUser')->andReturn($user);
        $userPasswordReset->expects('getConfirmation')->andReturn($confirmation);

        $this->repoMap['UserPasswordReset']->expects('fetchById')
            ->with($resetId)
            ->andReturn($userPasswordReset);

        $this->mockedSmServices[TemplateRenderer::class]->expects('renderBody')
            ->with(
                m::type(Message::class),
                SendForgotPassword::EMAIL_TEMPLATE,
                [
                    'callChargesUrl' => $callChargeUrl,
                    'enquiryEmail' => $licencingEmail,
                    'enquiryPhone' => $licencingPhone,
                    'resetUrl' => $resetUrl,
                ],
                'default'
            );

        $emailMessage = 'Email sent';
        $emailResult = new Result();
        $emailResult->addMessage($emailMessage);

        $this->expectedSideEffect(
            SendEmail::class,
            [
                'to' => $emailAddress
            ],
            $emailResult
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'User' => $userId,
                'UserPasswordReset' => $resetId,
            ],
            'messages' => [
                $emailMessage,
            ],
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
