<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Template as TemplateRepo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Group-by-name view of email templates (VOL-7238). Returns one row per `name` with the
 * aggregated locale + format coverage and a `primaryVariantId` chosen for the admin Edit
 * button.
 *
 * Distinct from {@see AvailableTemplates} which returns one row per (locale, format, name).
 * The grouped shape drives the admin CMS index — significantly less noise when there are
 * 6+ variants per template.
 */
final class AvailableTemplateGroups extends AbstractQueryHandler
{
    protected $repoServiceName = 'Template';

    #[\Override]
    public function handleQuery(QueryInterface $query): array
    {
        /** @var TemplateRepo $repo */
        $repo = $this->getRepo();

        return [
            'result' => $repo->fetchTemplateGroups($query),
            'count' => $repo->countTemplateGroups($query),
        ];
    }
}
