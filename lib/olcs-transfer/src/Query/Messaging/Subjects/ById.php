<?php

namespace Dvsa\Olcs\Transfer\Query\Messaging\Subjects;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName('backend/messaging/subjects/single')]
class ById extends AbstractQuery
{
    use Identity;
}
