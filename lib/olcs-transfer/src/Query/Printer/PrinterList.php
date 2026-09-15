<?php

/**
 * Get a list of Printers
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Printer;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTraitOptional;
use Dvsa\Olcs\Transfer\Query\OrderedTraitOptional;

/**
 * @Transfer\RouteName("backend/printer")
 */
final class PrinterList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTraitOptional;
    use OrderedTraitOptional;
}
