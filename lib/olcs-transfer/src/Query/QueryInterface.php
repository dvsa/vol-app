<?php

/**
 * Query Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query;

use Laminas\Stdlib\ArraySerializableInterface;

/**
 * Query Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface QueryInterface extends ArraySerializableInterface
{
    public static function create(array $data);
}
