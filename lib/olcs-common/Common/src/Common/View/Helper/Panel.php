<?php

declare(strict_types=1);

namespace Common\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class Panel extends AbstractHelper
{
    public const TYPE_SUCCESS = 'success';

    public const TYPE_CSS_CLASS_MAP = [
        'success' => 'govuk-panel--confirmation'
    ];

    /**
     * Renders a GOV UK Panel.
     */
    public function __invoke(string $type, string $title, string $body = ''): string
    {
        return $this->getView()->render('partials/panel', [
            'theme' => $this->mapTypeToTheme($type),
            'title' => $title,
            'body' => $body,
        ]);
    }

    protected function mapTypeToTheme(string $type): string
    {
        return static::TYPE_CSS_CLASS_MAP[$type] ?? '';
    }
}
