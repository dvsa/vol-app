<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Conviction Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="conviction",
 *    indexes={
 *        @ORM\Index(name="ix_conviction_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(name="ix_conviction_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_conviction_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_conviction_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_conviction_defendant_type", columns={"defendant_type"}),
 *        @ORM\Index(name="ix_conviction_conviction_category", columns={"conviction_category"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_conviction_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class Conviction extends AbstractConviction
{
    public const DEFENDANT_TYPE_DIRECTOR     = 'def_t_dir';
    public const DEFENDANT_TYPE_DRIVER       = 'def_t_driver';
    public const DEFENDANT_TYPE_ORGANISATION = 'def_t_op';
    public const DEFENDANT_TYPE_OTHER        = 'def_t_other';
    public const DEFENDANT_TYPE_OWNER        = 'def_t_owner';
    public const DEFENDANT_TYPE_PARTNER      = 'def_t_part';
    public const DEFENDANT_TYPE_TM           = 'def_t_tm';

    public const ERROR_CON_CAT = 'con-cat';

    public function updateConvictionCategory($type, $description)
    {
        if ($type === null && empty($description)) {
            throw new ValidationException(
                ['convictionCategory' => [self::ERROR_CON_CAT => 'You must specify a conviction category']]
            );
        }

        $this->convictionCategory = $type;
        $this->categoryText = $description;
    }
}
