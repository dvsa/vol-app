<?php

namespace Common\View\Helper;

use Common\Service\Cqrs\Query\CachingQueryService as QueryService;
use Dvsa\Olcs\Transfer\Query\System\InfoMessage\GetListActive;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\View\Helper\AbstractHelper;

/**
 * View helper to print system info messages
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class SystemInfoMessages extends AbstractHelper
{
    public const HTML_BLOCK = '<div class="system-messages">%s</div>';

    public const HTML_ITEM = '<div class="system-messages__wrapper"><p>%s</p></div>';

    /** @var AnnotationBuilder */
    protected $annotationBuilder;

    /** @var QueryService */
    protected $queryService;

    /** @var  array */
    protected $mssgs;

    public function __construct(
        AnnotationBuilder $annotationBuilder,
        QueryService $querySrv
    ) {
        $this->annotationBuilder = $annotationBuilder;
        $this->queryService = $querySrv;
    }

    /**
     * @param boolean $isInternal
     *
     * @return string
     */
    public function __invoke($isInternal)
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
