<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeatureToggle Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="feature_toggle",
 *    indexes={
 *        @ORM\Index(name="ix_feature_toggle_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_feature_toggle_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_feature_toggle_status", columns={"status"})
 *    }
 * )
 */
class FeatureToggle extends AbstractFeatureToggle
{
    public const ACTIVE_STATUS = 'always-active';
    public const INACTIVE_STATUS = 'inactive';
    public const CONDITIONAL_STATUS = 'conditionally-active';
    public const INTERNAL_SURRENDER = 'internal_surrender';
    public const SELFSERVE_SURRENDER = 'ss_surrender';
    public const BACKEND_SURRENDER = 'back_surrender';
    public const MESSAGING = 'messaging';
    public const USE_NEW_ADDRESS_SERVICE = 'use_new_address_service';
    public const TRANSPORT_CONSULTANT_ROLE = 'transport_consultant_role';
    public const LETTERS_DATABASE_DRIVEN = 'letters_database_driven';

    public static function create(/*string*/ $configName, /*string*/ $friendlyName, RefData $status)/*: FeatureToggle */
    {
        $instance = new self();
        $instance->configName = $configName;
        $instance->friendlyName = $friendlyName;
        $instance->status = $status;

        return $instance;
    }

    public function update(/*string*/ $configName, /*string*/ $friendlyName, RefData $status): FeatureToggle
    {
        $this->configName = $configName;
        $this->friendlyName = $friendlyName;
        $this->status = $status;

        return $this;
    }
}
