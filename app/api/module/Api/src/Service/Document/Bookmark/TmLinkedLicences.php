<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Transfer\Query\TmResponsibilities\TmResponsibilitiesList as Qry;

/**
 * Transport manager linked licences bookmark
 */
class TmLinkedLicences extends DynamicBookmark
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

        if (!empty($this->data['result'])) {
            foreach ($this->data['result'] as $tmLic) {
                if (isset($tmLic['licence']['licNo']) && isset($tmLic['licence']['organisation']['name'])) {
                    $result[] = sprintf(
                        '%s: %s',
                        $tmLic['licence']['licNo'],
                        $tmLic['licence']['organisation']['name']
                    );
                }
            }
        }

        return implode("\n", $result);
    }
}
