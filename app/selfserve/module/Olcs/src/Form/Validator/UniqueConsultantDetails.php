<?php

namespace Olcs\Form\Validator;

use Laminas\Validator\AbstractValidator;
use Olcs\Session\ConsultantRegistration;

class UniqueConsultantDetails extends AbstractValidator
{
    public const NOT_UNIQUE = 'notUnique';

    protected $messageTemplates = [
        self::NOT_UNIQUE => '%value% was used for the operator administrator account. You must use a different username and email for your consultant account.'
    ];

    protected $session;

    public function __construct(ConsultantRegistration $session)
    {
        $this->session = $session;
        parent::__construct();
    }

    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        $operatorDetails = $this->session->getOperatorDetails();

        if (
            ($value === $operatorDetails['fields']['loginId'])
            || ($value === $operatorDetails['fields']['emailAddress'])
        ) {
            $this->error(self::NOT_UNIQUE);
            return false;
        }
        return true;
    }
}
