<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\CaseBundle as Qry;

/**
 * Application Trailer Limit
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationTrailerLimit extends DynamicBookmark
{
    /**
     * Get query
     *
     * @param array $data data
     *
     * @return Qry
     */
    public function getQuery(array $data)
    {
        $bundle = ['application'];
        return Qry::create(['id' => $data['case'], 'bundle' => $bundle]);
    }

    /**
     * Render
     *
     * @return int
     */
    public function render()
    {
        return $this->data['application']['totAuthTrailers'] ?? 0;
    }
}
