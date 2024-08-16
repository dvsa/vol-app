<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\ContactDetail;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Get a list of Countries, optionally filtered by flags
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class CountrySelectList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'Country';
}
