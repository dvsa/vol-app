<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LaEntity;

/**
 * Class LocalAuthorityMissing
 * @package Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData
 */
class LocalAuthorityMissing extends AbstractValidator
{
    public const LA_MISSING_ERROR = 'la-missing-error';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::LA_MISSING_ERROR => 'According to the stops, these Local Authorities are missing from the data: %value%'
    ];

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value, $context = [])
    {
        /**
         * @var LaEntity $naptan
         * @var ArrayCollection $localAuthorities
         * @var ArrayCollection $naptanLocalAuthorities
         */
        $localAuthorities = $value['localAuthoritys']; //grammar mismatch result of entity array collection name
        $naptanLocalAuthorities = $value['naptanAuthorities'];

        $missing = [];

        foreach ($naptanLocalAuthorities as $naptan) {
            if (!$localAuthorities->contains($naptan)) {
                $missing[] = $naptan->getDescription();
            }
        }

        if (!empty($missing)) {
            $this->error(self::LA_MISSING_ERROR, implode(', ', $missing));
            return false;
        }

        return true;
    }
}
