<?php

namespace Dvsa\Olcs\Api\Entity\Legacy;

use Doctrine\ORM\Mapping as ORM;

/**
 * LegacyRecommendation Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="legacy_recommendation",
 *    indexes={
 *        @ORM\Index(name="ix_legacy_recommendation_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_legacy_recommendation_from_user_id", columns={"from_user_id"}),
 *        @ORM\Index(name="ix_legacy_recommendation_to_user_id", columns={"to_user_id"}),
 *        @ORM\Index(name="ix_legacy_recommendation_action_id", columns={"action_id"}),
 *        @ORM\Index(name="ix_legacy_recommendation_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_legacy_recommendation_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class LegacyRecommendation extends AbstractLegacyRecommendation
{
}
