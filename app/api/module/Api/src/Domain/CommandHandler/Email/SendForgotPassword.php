<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendForgotPassword as SendForgotPasswordCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\UserPasswordReset as UserPasswordResetRepo;
use Dvsa\Olcs\Api\Domain\TranslationLoaderAwareInterface;
use Dvsa\Olcs\Api\Domain\TranslationLoaderAwareTrait;
use Dvsa\Olcs\Api\Entity\User\UserPasswordReset as UserPasswordResetEntity;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

final class SendForgotPassword extends AbstractCommandHandler implements EmailAwareInterface, TranslationLoaderAwareInterface
{
    use EmailAwareTrait;
    use TranslationLoaderAwareTrait;

    protected $repoServiceName = 'UserPasswordReset';

    public const string EMAIL_TEMPLATE = 'auth-forgot-password';
    public const string EMAIL_SUBJECT = 'auth.forgot-password.email.subject';
    public const string CALL_CHARGE_REPLACEMENT = '{{CALL_CHARGES_INFO}}';
    public const string OPERATOR_LICENSING_PHONE_REPLACEMENT = '{{OPERATOR_LICENSING_PHONE}}';
    public const string OPERATOR_LICENSING_EMAIL_REPLACEMENT = '{{OPERATOR_LICENSING_EMAIL}}';
    public const string RESET_URL = 'http://%s/auth/reset-password?confirmationId=%s&username=%s&tokenId=a';

    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof SendForgotPasswordCmd);
        $resetId = $command->getId();
        $repo = $this->getRepo();
        assert($repo instanceof UserPasswordResetRepo);
        $userPasswordReset = $this->getRepo()->fetchById($resetId);
        assert($userPasswordReset instanceof UserPasswordResetEntity);
        $user = $userPasswordReset->getUser();

        $message = new Message(
            $user->getContactDetails()->getEmailAddress(),
            self::EMAIL_SUBJECT
        );

        $message->setTranslateToWelsh($user->getTranslateToWelsh());

        //brings the translation replacements in so phone numbers and emails can be updated via internal
        $replacements = $this->translationLoader->loadReplacements();

        $resetUrl = sprintf(
            self::RESET_URL,
            $command->getRealm(),
            $userPasswordReset->getConfirmation(),
            $user->getLoginId()
        );

        $this->result->merge(
            $this->sendEmailTemplate(
                $message,
                self::EMAIL_TEMPLATE,
                [
                    'callChargesUrl' => $replacements[self::CALL_CHARGE_REPLACEMENT],
                    'enquiryEmail' => $replacements[self::OPERATOR_LICENSING_EMAIL_REPLACEMENT],
                    'enquiryPhone' => $replacements[self::OPERATOR_LICENSING_PHONE_REPLACEMENT],
                    'resetUrl' => $resetUrl,
                ]
            )
        );

        $this->result->addId('UserPasswordReset', $resetId);
        $this->result->addId('User', $user->getId());

        return $this->result;
    }
}
