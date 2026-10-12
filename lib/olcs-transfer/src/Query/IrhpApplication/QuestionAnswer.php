<?php

/**
 * Retrieve a question/answer data for a given IRHP application
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName('backend/irhp-application/single/question-answer')]
class QuestionAnswer extends AbstractQuery
{
    use Identity;
}
