<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\RulesValidator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Licence
 * @package Dvsa\Olcs\Api\Service\Ebsr\RulesValidator
 */
class Licence extends AbstractValidator
{
    public const LICENCE_MISSING_ERROR = 'licence-missing-error';
    public const LICENCE_INACTIVE_ERROR = 'licence-inactive-error';
    public const LICENCE_TYPE_ERROR = 'licence-type-error';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::LICENCE_MISSING_ERROR => 'This licence number was not found for your organisation',
        self::LICENCE_INACTIVE_ERROR => 'The licence is not allowed to accept EBSR submissions (licence not active)',
        self::LICENCE_TYPE_ERROR => 'The licence type is not allowed to accept EBSR submissions (PSV only)'
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
         * @var OrganisationEntity $organisation
         * @var ArrayCollection $licenceCollection
         * @var LicenceEntity $licence
         */
        $organisation = $context['organisation'];
        $licenceCollection = $organisation->getLicenceByLicNo($value['licNo']);

        if ($licenceCollection->isEmpty()) {
            $this->error(self::LICENCE_MISSING_ERROR);
            return false;
        }

        $licence = $licenceCollection[0];

        if ($licence->getGoodsOrPsv()->getId() !== LicenceEntity::LICENCE_CATEGORY_PSV) {
            $this->error(self::LICENCE_TYPE_ERROR);
            return false;
        }

        $validLicenceStates = [LicenceEntity::LICENCE_STATUS_VALID,LicenceEntity::LICENCE_STATUS_CURTAILED, LicenceEntity::LICENCE_STATUS_SUSPENDED];

        if (!in_array($licence->getStatus()->getId(), $validLicenceStates)) {
            $this->error(self::LICENCE_INACTIVE_ERROR);
            return false;
        }

        return true;
    }
}
