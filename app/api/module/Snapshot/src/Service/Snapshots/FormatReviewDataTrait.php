<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots;

use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Snapshot\Service\Formatter\Address;

trait FormatReviewDataTrait
{
    /**
     * Format date
     *
     * @param \DateTime|string $date   Date
     * @param string           $format Date Format
     *
     * @return bool|string
     */
    protected function formatDate($date, $format = 'd M Y')
    {
        if ($date instanceof \DateTime) {
            return $date->format($format);
        }

        return date($format, strtotime($date));
    }

    /**
     * Format address
     *
     * @param array|\Dvsa\Olcs\Api\Entity\ContactDetails\Address $address Address data or entity
     *
     * @return string
     */
    protected function formatFullAddress($address)
    {
        return Address::format($address, ['addressFields' => 'FULL']);
    }

    /**
     * Format Yes/No
     *
     * @param string $value Y or N
     *
     * @return string
     */
    protected function formatYesNo($value)
    {
        return $value === 'Y' ? 'Yes' : 'No';
    }

    /**
     * Translate
     *
     * @param string $string Text or Translation key
     *
     * @return string
     */
    protected function translate($string)
    {
        if ($string === null) {
            return '';
        }
        return $this->translator->translate($string);
    }

    /**
     * Format Full name
     *
     * @param Person $person Person Entity
     *
     * @return string
     */
    protected function formatPersonFullName(Person $person)
    {
        $parts = [];

        if ($person->getTitle() !== null) {
            $parts[] = $person->getTitle()->getDescription();
        }

        $parts[] = $person->getForename();
        $parts[] = $person->getFamilyName();

        return implode(' ', $parts);
    }

    /**
     * Format address short
     *
     * @param array|\Dvsa\Olcs\Api\Entity\ContactDetails\Address $address Address data or entity
     *
     * @return string
     */
    protected function formatShortAddress($address)
    {
        return Address::format($address);
    }

    /**
     * Translate and replace
     *
     * @param string $translationKey Tranlation key
     * @param array  $arguments      Values for replacement
     *
     * @return string
     */
    protected function translateReplace($translationKey, array $arguments)
    {
        return vsprintf(
            $this->translator->translate($translationKey),
            $arguments
        );
    }
}
