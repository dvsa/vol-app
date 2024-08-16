<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\ApplicationBundle as Qry;

/**
 * Interim Licence Type bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class InterimLicenceType extends AbstractLicenceType
{
    public const QUERY_CLASS  = Qry::class;
    public const DATA_KEY = 'application';
}
