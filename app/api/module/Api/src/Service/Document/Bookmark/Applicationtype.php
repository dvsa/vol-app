<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * Applicationtype bookmark
 */
class Applicationtype extends DynamicBookmark
{
    /**
     * Get the Query to populate the bookmark
     *
     * @param array $data Known data
     *
     * @return Bookmark\ApplicationBundle|null
     */
    public function getQuery(array $data)
    {
        $bundle = ['licenceType'];
        if (!empty($data['application'])) {
            return Bookmark\ApplicationBundle::create(['id' => $data['application'], 'bundle' => $bundle]);
        }
        if (!empty($data['case'])) {
            return Bookmark\ApplicationBundle::create(['case' => $data['case'], 'bundle' => $bundle]);
        }

        return null;
    }

    /**
     * Render bookmark
     *
     * @return string
     */
    public function render()
    {
        return $this->data['licenceType']['description'];
    }
}
