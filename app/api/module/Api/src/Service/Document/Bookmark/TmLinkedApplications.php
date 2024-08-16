<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Transfer\Query\TmResponsibilities\TmResponsibilitiesList as Qry;

/**
 * Transport manager linked applications bookmark
 */
class TmLinkedApplications extends DynamicBookmark
{
    /**
     * Get the DTO which will populate data for the bookmark
     *
     * @param array $data Data
     *
     * @return Qry
     */
    public function getQuery(array $data)
    {
        return Qry::create(['transportManager' => $data['transportManager']]);
    }

    /**
     * Render the bookmark
     *
     * @return string
     */
    public function render()
    {
        $result = [];

        if (!empty($this->data['tmApplications'])) {
            foreach ($this->data['tmApplications'] as $tmApp) {
                if (
                    isset($tmApp['application']['licence']['licNo'])
                    && isset($tmApp['application']['id'])
                    && isset($tmApp['application']['licence']['organisation']['name'])
                ) {
                    $result[] = sprintf(
                        '%s/%d: %s',
                        $tmApp['application']['licence']['licNo'],
                        $tmApp['application']['id'],
                        $tmApp['application']['licence']['organisation']['name']
                    );
                }
            }
        }

        return implode("\n", $result);
    }
}
