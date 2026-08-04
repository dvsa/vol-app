<?php

/**
 * Group-by-name view of editable email templates. Returns one row per template `name`,
 * with the per-variant locale + format coverage as aggregated arrays. Drives the admin
 * CMS index at /admin/email-templates (VOL-7238 deliverable 7) so the list isn't ~180
 * (locale × format × name) rows.
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
 * @Transfer\RouteName("backend/template/available-template-groups")
 */
class AvailableTemplateGroups extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
    use EmailTemplateCategory;

    /**
     * Filter to names that have at least one variant in the given format. Empty = no filter.
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
