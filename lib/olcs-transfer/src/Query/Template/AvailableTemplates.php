<?php

/**
 * Get templates available for editing
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Template;

use Dvsa\Olcs\Transfer\FieldType\Traits\EmailTemplateCategory;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/template/available-templates")
 */
class AvailableTemplates extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
    use EmailTemplateCategory;

    /**
     * Filter the list by template `format` column. Empty / unset = no filter.
     *
     * @Transfer\Optional
     * @var string
     */
    protected $format = '';

    public function getFormat(): string
    {
        return (string) $this->format;
    }
}
