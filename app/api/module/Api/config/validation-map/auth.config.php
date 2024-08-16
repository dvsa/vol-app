<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NotIsAnonymousUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;

return [
    CommandHandler\Auth\Login::class => NoValidationRequired::class,
    CommandHandler\Auth\LoginFactory::class => NoValidationRequired::class,
    CommandHandler\Auth\ChangePassword::class => NotIsAnonymousUser::class,
    CommandHandler\Auth\ChangePasswordFactory::class => NotIsAnonymousUser::class,
    CommandHandler\Auth\ForgotPassword::class => NoValidationRequired::class,
    CommandHandler\Auth\ForgotPasswordFactory::class => NoValidationRequired::class,
    CommandHandler\Auth\ResetPassword::class => NoValidationRequired::class,
    CommandHandler\Auth\ResetPasswordFactory::class => NoValidationRequired::class,
    CommandHandler\Auth\RefreshTokens::class => NoValidationRequired::class,
    CommandHandler\Auth\RefreshTokensFactory::class => NoValidationRequired::class,
    CommandHandler\Auth\ChangeExpiredPassword::class => NoValidationRequired::class,
    CommandHandler\Auth\ChangeExpiredPasswordFactory::class => NoValidationRequired::class,
];
