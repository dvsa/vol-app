<?php

namespace Olcs\Session;

/**
 * Class ConsultantRegistration
 *
 * @template-extends \Laminas\Session\Container<string, mixed>
 */
class ConsultantRegistration extends \Laminas\Session\Container
{
    public const SESSION_NAME = 'ConsultantRegistration';
    protected const OPERATOR_DETAILS = 'operatorDetails';
    protected const CONSULTANT_DETAILS = 'consultantDetails';

    public function __construct()
    {
        parent::__construct(self::SESSION_NAME);
    }

    public function setOperatorDetails(array $details): self
    {
        $this->offsetSet(self::OPERATOR_DETAILS, $details);
        return $this;
    }

    public function getOperatorDetails(): ?array
    {
        return $this->offsetGet(self::OPERATOR_DETAILS);
    }

    public function setConsultantDetails(array $details): self
    {
        $this->offsetSet(self::CONSULTANT_DETAILS, $details);
        return $this;
    }

    public function getConsultantDetails(): ?array
    {
        return $this->offsetGet(self::CONSULTANT_DETAILS);
    }

    public function clear(): void
    {
        $this->getManager()->getStorage()->clear(self::SESSION_NAME);
    }
}
