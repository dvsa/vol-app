<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Stub;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName("test/ordered")]
final class OrderedQueryStub extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTrait;
}
