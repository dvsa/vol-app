<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * S9AuthorisedDecision
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class S9AuthorisedDecision extends StatementFlatAbstract
{
    public const FORMATTER = null;
    public const FIELD  = 'authorisersDecision';
    public const SRCH_VAL_KEY = 'statement';
}
