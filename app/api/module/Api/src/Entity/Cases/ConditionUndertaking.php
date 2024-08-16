<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConditionUndertaking Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="condition_undertaking",
 *    indexes={
 *        @ORM\Index(name="ix_condition_undertaking_added_via", columns={"added_via"}),
 *        @ORM\Index(name="ix_condition_undertaking_attached_to", columns={"attached_to"}),
 *        @ORM\Index(name="ix_condition_undertaking_condition_type", columns={"condition_type"}),
 *        @ORM\Index(name="ix_condition_undertaking_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_condition_undertaking_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_condition_undertaking_operating_centre_id", columns={"operating_centre_id"}),
 *        @ORM\Index(name="ix_condition_undertaking_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_condition_undertaking_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_condition_undertaking_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(
 *            name="ix_condition_undertaking_lic_condition_variation_id",
 *            columns={"lic_condition_variation_id"}
 *        ),
 *        @ORM\Index(name="ix_condition_undertaking_approval_user_id", columns={"approval_user_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_condition_undertaking_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
class ConditionUndertaking extends AbstractConditionUndertaking
{
    public const ATTACHED_TO_LICENCE = 'cat_lic';
    public const ATTACHED_TO_OPERATING_CENTRE = 'cat_oc';

    public const ADDED_VIA_CASE = 'cav_case';
    public const ADDED_VIA_LICENCE = 'cav_lic';
    public const ADDED_VIA_APPLICATION = 'cav_app';

    public const TYPE_CONDITION = 'cdt_con';
    public const TYPE_UNDERTAKING = 'cdt_und';

    public const SMALL_VEHICLE_UNDERTAKINGS = 'Small vehicles undertakings';

    public const LIGHT_GOODS_VEHICLE_UNDERTAKINGS =
        'All authorised vehicles shall not exceed 3,500 Kilograms (kg), including when combined with a trailer.';

    public const ACTION_ADD = 'A';
    public const ACTION_UPDATE = 'U';
    public const ACTION_DELETE = 'D';

    public const CATEGORY_OTHER = 'cu_cat_other';

    /**
     * Construct Condition Undertaking entity
     *
     * @param RefData $conditionType Condition Type
     * @param string  $isFulfilled   'Yes|No' fulfilled
     * @param string  $isDraft       'Yes|No' draft
     */
    public function __construct(
        RefData $conditionType,
        $isFulfilled,
        $isDraft
    ) {
        parent::__construct();

        $this->setConditionType($conditionType);
        $this->setIsDraft($isDraft);
        $this->setIsFulfilled($isFulfilled);
    }
}
