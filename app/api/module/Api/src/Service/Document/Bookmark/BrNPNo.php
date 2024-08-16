<?php

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PublicationLinkBundle as Qry;

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class BrNPNo extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        if (!isset($data['busRegId'])) {
            return null;
        }

        return Qry::create(['busReg' => $data['busRegId'], 'bundle' => ['publication']]);
    }

    public function render()
    {
        if (empty($this->data['Results'])) {
            return '';
        }

        // get the last record
        $publicationLink = array_pop($this->data['Results']);

        return !empty($publicationLink['publication']['publicationNo']) ?
            $publicationLink['publication']['publicationNo'] : '';
    }
}
