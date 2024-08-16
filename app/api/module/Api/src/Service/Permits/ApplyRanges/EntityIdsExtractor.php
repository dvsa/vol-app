<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

class EntityIdsExtractor
{
    /**
     * Derive an array of numeric ids by calling getId on an array of entities
     *
     *
     * @return array
     */
    public function getExtracted(array $entities)
    {
        $ids = [];

        foreach ($entities as $entity) {
            $ids[] = $entity->getId();
        }

        return $ids;
    }
}
