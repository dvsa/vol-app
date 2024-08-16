<?php

namespace Dvsa\Olcs\Api\Entity\Opposition;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Opposer Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="opposer",
 *    indexes={
 *        @ORM\Index(name="ix_opposer_contact_details_id", columns={"contact_details_id"}),
 *        @ORM\Index(name="ix_opposer_opposer_type", columns={"opposer_type"}),
 *        @ORM\Index(name="ix_opposer_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_opposer_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_opposer_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
class Opposer extends AbstractOpposer
{
    public function __construct(
        ContactDetails $contactDetails,
        RefData $opposerType = null,
        RefData $oppositionType = null
    ) {
        $this->setContactDetails($contactDetails);

        $this->checkObjectionOpposerType($opposerType, $oppositionType);

        $this->setOpposerType($opposerType);
    }

    private function checkObjectionOpposerType($opposerType, $oppositionType)
    {
        if (
            (!is_null($oppositionType) &&
                ($oppositionType->getId() == Opposition::OPPOSITION_TYPE_ENV)) &&
            (empty($opposerType->getId()))
        ) {
            throw new InvalidArgumentException('Environmental objections must specify a type of opposer');
        }
    }

    /**
     * @param array $opposerParams Array of data
     */
    public function update(array $opposerParams)
    {
        $this->checkObjectionOpposerType($opposerParams['opposerType'], $opposerParams['oppositionType']);

        $this->setOpposerType($opposerParams['opposerType']);
    }
}
