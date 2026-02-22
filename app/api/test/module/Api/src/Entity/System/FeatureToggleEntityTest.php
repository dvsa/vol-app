<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\System;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * FeatureToggle Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class FeatureToggleEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCreateUpdate(): void
    {
        $status = Entity::ACTIVE_STATUS;
        $refDataStatus = new RefData($status);
        $friendlyName = 'friendly name';
        $configName = 'config name';

        $updateStatus = Entity::INACTIVE_STATUS;
        $updateRefDataStatus = new RefData($updateStatus);
        $updateFriendlyName = 'updated friendly name';
        $updateConfigName = 'updated config name';

        $entity = Entity::create($configName, $friendlyName, $refDataStatus);

        $this->assertEquals($friendlyName, $entity->getFriendlyName());
        $this->assertEquals($configName, $entity->getConfigName());
        $this->assertEquals($status, $entity->getStatus()->getId());

        $entity->update($updateConfigName, $updateFriendlyName, $updateRefDataStatus);

        $this->assertEquals($updateFriendlyName, $entity->getFriendlyName());
        $this->assertEquals($updateConfigName, $entity->getConfigName());
        $this->assertEquals($updateRefDataStatus, $entity->getStatus()->getId());
    }
}
