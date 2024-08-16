<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\InterimUnlinkedTm as Qry;

/**
 * Returns all the transport managers associated with a given application
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class InterimUnlinkedTm extends DynamicBookmark
{
    /**
     * Get the query data to fetch back the relevant TMs
     *
     * @param array $data
     *
     * @return array
     */
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['application']]);
    }

    /**
     * Return the listed TMs on the application
     *
     * @return string
     */
    public function render()
    {
        if ($this->data['licenceType']['id'] === Licence::LICENCE_TYPE_RESTRICTED) {
            return 'N/A';
        }
        $transportManagers = $this->data['transportManagers'];

        if (count($transportManagers) === 0) {
            return 'None added as part of this application';
        }

        $output = [];
        foreach ($transportManagers as $tm) {
            $person = $tm['transportManager']['homeCd']['person'];
            $output[] = Formatter\Name::format($person);
        }

        return implode("\n", $output);
    }
}
