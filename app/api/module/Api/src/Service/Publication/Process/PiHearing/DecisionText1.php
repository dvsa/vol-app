<?php

/**
 * Decision text 1
 */

namespace Dvsa\Olcs\Api\Service\Publication\Process\PiHearing;

use Dvsa\Olcs\Api\Service\Publication\Process\Text1 as AbstractText1;

/**
 * Class DecisionText1
 * @package Dvsa\Olcs\Api\Service\Publication\Process\PiHearing
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class DecisionText1 extends AbstractText1
{
    protected $pi = 'Public Inquiry (%s) held at %s, on %s commenced at %s';
}
