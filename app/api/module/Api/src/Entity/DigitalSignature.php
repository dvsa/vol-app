<?php

namespace Dvsa\Olcs\Api\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Service\GovUkAccount\Data\Attributes;

/**
 * DigitalSignature Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="digital_signature",
 *    indexes={
 *        @ORM\Index(name="ix_digital_signature_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_digital_signature_tma_id", columns={"tma_id"})
 *    }
 * )
 */
class DigitalSignature extends AbstractDigitalSignature
{
    public const ERR_INVALID_SIG = 'Signature attributes missing name or birth date information';

    public function addSignatureInfo(Attributes $attributes, string $response)
    {
        if (!$attributes->isValidSignature()) {
            throw new \Exception(self::ERR_INVALID_SIG);
        }

        $this->attributes = json_encode($attributes->getArrayCopy());
        $this->samlResponse = $response;
    }

    /**
     * Set the attributes
     *
     * @param array $attributes new value being set
     *
     * @return DigitalSignature
     */
    public function setAttributesArray(array $attributes)
    {
        $this->attributes = json_encode($attributes);

        return $this;
    }

    /**
     * Get the attributes
     *
     * @return array
     */
    public function getAttributesArray()
    {
        if ($this->attributes === null) {
            return [];
        }
        $array = json_decode($this->attributes, true);
        return is_array($array) ? $array : [];
    }

    /**
     * Get the full name of the person for attributes
     *
     * @return string
     */
    public function getSignatureName()
    {
        $attributes = $this->getAttributesArray();

        $names = [];
        if (!empty($attributes[Attributes::FIRST_NAME])) {
            $names[] = $attributes[Attributes::FIRST_NAME];
        }
        if (!empty($attributes[Attributes::SURNAME])) {
            $names[] = $attributes[Attributes::SURNAME];
        }

        foreach ($names as &$namePart) {
            $namePart = ucfirst(strtolower((string) $namePart));
        }

        return implode(' ', $names);
    }

    /**
     * Get DOB of the person from attributes
     *
     * @return \DateTime|null
     */
    public function getDateOfBirth()
    {
        $attributes = $this->getAttributesArray();

        if (!empty($attributes[Attributes::DATE_OF_BIRTH])) {
            return $attributes[Attributes::DATE_OF_BIRTH];
        }

        return null;
    }
}
