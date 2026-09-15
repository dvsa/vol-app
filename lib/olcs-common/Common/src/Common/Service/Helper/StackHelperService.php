<?php

/**
 * Stack Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Helper;

/**
 * Stack Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class StackHelperService
{
    public function getStackValue(array $stack, array $stackReference)
    {
        $stackRef = &$stack;

        foreach ($stackReference as $level) {
            if (!isset($stackRef[$level])) {
                return null;
            }

            $stackRef = &$stackRef[$level];
        }

        return $stackRef;
    }
}
