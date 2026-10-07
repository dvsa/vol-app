<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Stub;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTraitOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("test/ordered-optional")
 */
final class OrderedOptionalQueryStub extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTraitOptional;
}
