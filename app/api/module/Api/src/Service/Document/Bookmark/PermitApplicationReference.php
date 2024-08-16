<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\IrhpPermitBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * PermitApplicationReference - fetch permit application reference
 *
 * @author Henry White <henry.white@capgemini.com>
 */
class PermitApplicationReference extends DynamicBookmark
{
    /**
     * Get the Query which can get the data required for the bookmark
     *
     * @param array $data
     * @return Qry|\Dvsa\Olcs\Transfer\Query\AbstractQuery
     */
    public function getQuery(array $data)
    {
        return Qry::create(
            [
                'id' => $data['irhpPermit'],
                'bundle' => [
                    'irhpPermitApplication'
                ]
            ]
        );
    }

    /**
     * Renders the bookmark
     *
     * @return string
     */
    public function render()
    {
        if (isset($this->data['irhpPermitApplication']['relatedApplication']['applicationRef'])) {
            return trim((string) $this->data['irhpPermitApplication']['relatedApplication']['applicationRef']);
        }

        return '';
    }
}
