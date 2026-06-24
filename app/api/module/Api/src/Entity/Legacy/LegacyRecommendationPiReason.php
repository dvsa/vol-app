<?php

namespace Dvsa\Olcs\Api\Entity\Legacy;

use Doctrine\ORM\Mapping as ORM;

/**
 * LegacyRecommendationPiReason Entity
 */
#[ORM\Table(name: 'legacy_recommendation_pi_reason')]
#[ORM\Index(name: 'ix_legacy_recommendation_pi_reason_legacy_recommendation_id', columns: ['legacy_recommendation_id'])]
#[ORM\Index(name: 'ix_legacy_recommendation_pi_reason_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_legacy_recommendation_pi_reason_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Index(name: 'ix_legacy_recommendation_pi_reason_legacy_pi_reason_id', columns: ['legacy_pi_reason_id'])]
#[ORM\Entity]
class LegacyRecommendationPiReason extends AbstractLegacyRecommendationPiReason
{
}
