<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * S43AuthorisedDecision
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class S43AuthorisedDecision extends StatementFlatAbstract
{
    public const FORMATTER = null;
    public const FIELD  = 'authorisersDecision';
    public const SRCH_VAL_KEY = 'statement';
}
