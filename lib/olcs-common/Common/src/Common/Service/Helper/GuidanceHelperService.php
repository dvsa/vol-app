<?php

/**
 * Guidance Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Helper;

use Laminas\View\Helper\Placeholder;

/**
 * Guidance Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GuidanceHelperService
{
    public function __construct(private Placeholder $placeholder)
    {
    }

    public function append(string $message): void
    {
        $this->placeholder->getContainer('guidance')->append($message);
    }
}
