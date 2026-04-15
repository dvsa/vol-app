<?php

declare(strict_types=1);

namespace Common\View\Helper;

use Common\Service\Cqrs\Query\CachingQueryService as QueryService;
use Dvsa\Olcs\Transfer\Query\System\InfoMessage\GetListActive;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\View\Helper\AbstractHelper;

/**
 * View helper to print system info messages
 */
class SystemInfoMessages extends AbstractHelper
{
    public const string HTML_BLOCK = '<div class="govuk-notification-banner" role="region" aria-labelledby="govuk-notification-banner-title" data-module="govuk-notification-banner">
  <div class="govuk-notification-banner__header">
    <h2 class="govuk-notification-banner__title" id="govuk-notification-banner-title">
      Important
    </h2>
  </div>
  <div class="govuk-notification-banner__content">
    %s
  </div>
</div>';
    public const string HTML_ITEM = '<p class="govuk-notification-banner__heading">%s</p>';
    protected ?array $mssgs;

    public function __construct(
        private readonly AnnotationBuilder $annotationBuilder,
        private readonly QueryService $queryService
    ) {
    }

    public function __invoke(bool $isInternal): ?string
    {
        $this->getData($isInternal);

        return $this->render();
    }

    private function render(): string|null
    {
        if ($this->mssgs === null || !isset($this->mssgs['results']) || $this->mssgs['count'] === 0) {
            return null;
        }

        $items = [];
        foreach ($this->mssgs['results'] as $msg) {
            $items[] = sprintf(self::HTML_ITEM, htmlspecialchars($msg['description']));
        }

        return sprintf(self::HTML_BLOCK, implode('', $items));
    }

    private function getData(bool $isInternal): static
    {
        $qry = GetListActive::create(['isInternal' => $isInternal]);

        $qryContainer = $this->annotationBuilder->createQuery($qry);
        $response = $this->queryService->send($qryContainer);

        $this->mssgs = ($response->isOk() ? $response->getResult() : null);

        return $this;
    }
}
