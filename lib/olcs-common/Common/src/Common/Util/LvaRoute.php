<?php

/**
 * Lva Route
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Util;

use Laminas\Router\Http\Segment;

/**
 * Lva Route
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LvaRoute extends Segment
{
    #[\Override]
    public function assemble(array $params = [], array $options = [])
    {
        if (isset($params['action']) && $params['action'] === 'index') {
            $params['action'] = null;
        }

        return parent::assemble($params, $options);
    }
}
