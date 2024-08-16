<?php

/**
 * Hearing text 1
 */

namespace Dvsa\Olcs\Api\Service\Publication\Process\PiHearing;

use Dvsa\Olcs\Api\Service\Publication\Process\Text1 as AbstractText1;

/**
 * Class HearingText1
 * @package Dvsa\Olcs\Api\Service\Publication\Process\PiHearing
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class HearingText1 extends AbstractText1
{
    protected $pi = 'Public Inquiry (%s) to be held at %s, on %s commencing at %s';
}
