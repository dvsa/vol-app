<?php

namespace Olcs\Session;

use Laminas\Session\Container;

/**
 * Class ConsultantRegistration
 *
 * @template-extends \Laminas\Session\Container<string, mixed>
 */
class ConsultantRegistration extends Container
{
    public const SESSION_NAME = 'ConsultantRegistration';
    protected const OPERATOR_DETAILS = 'operatorDetails';
    protected const CONSULTANT_DETAILS = 'consultantDetails';

    protected const OPERATOR_ADMIN   = 'operatorAdmin';
    protected const OPERATOR_LICENCE = 'operatorLicence';

    public function __construct()
    {
        parent::__construct(self::SESSION_NAME);
    }

    public function setExistingLicence(string $licenceNumber): self
    {
        $this->offsetSet(self::OPERATOR_LICENCE, $licenceNumber);
        return $this;
    }

    public function getExistingLicence(): ?string
    {
        return $this->offsetGet(self::OPERATOR_LICENCE);
    }

    public function setOperatorAdmin(bool $hasOperatorAdmin): self
    {
        $this->offsetSet(self::OPERATOR_ADMIN, $hasOperatorAdmin);
        return $this;
    }

    public function getOperatorAdmin(): ?bool
    {
        return $this->offsetGet(self::OPERATOR_ADMIN);
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
