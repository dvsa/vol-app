<?php

declare(strict_types=1);

namespace Common\Validator;

use Common\RefData;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;

class MustRemainOperatorAdmin extends AbstractValidator
{
    public const NOT_OPERATOR_ADMIN = 'NOT_OPERATOR_ADMIN';

    protected $messageTemplates = [
        self::NOT_OPERATOR_ADMIN => 'The user must remain an Operator Admin',
    ];

    #[\Override]
    public function isValid($value)
    {
        if ($value === RefData::ROLE_OPERATOR_ADMIN) {
            return true;
        }

        $this->error(self::NOT_OPERATOR_ADMIN);

        return false;
    }
}
