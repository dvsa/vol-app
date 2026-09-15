<?php

namespace Dvsa\Olcs\Transfer\Query\Publication;

use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;

/**
 * Class PublicationLinkList
 * @Transfer\RouteName("backend/publication/link")
 */
class PublicationLinkList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
    use LicenceOptional;
    use ApplicationOptional;

    public function setLicence(mixed $licence)
    {
        $this->licence = $licence;
    }

    public function setApplication(mixed $application)
    {
        $this->application = $application;
    }
}
