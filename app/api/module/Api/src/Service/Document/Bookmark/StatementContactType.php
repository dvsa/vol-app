<?php

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\StatementBundle as Qry;

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class StatementContactType extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        if (!isset($data['statement'])) {
            return null;
        }

        return Qry::create(['id' => $data['statement'], 'bundle' => ['contactType']]);
    }

    public function render()
    {
        return $this->data['contactType']['description'] ?? '';
    }
}
