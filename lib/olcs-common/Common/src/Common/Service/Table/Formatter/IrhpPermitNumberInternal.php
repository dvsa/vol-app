<?php

/**
 * IrhpPermitNumberInternal formatter
 */

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Util\Escape;

class IrhpPermitNumberInternal implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    #[\Override]
    public function format($row, $column = null)
    {
        $route = 'licence/irhp-permits/permit';
        $params = [
            'licence' => $row['irhpPermitApplication']['relatedApplication']['licence']['id'],
        ];
        $options = [
            'query' => [
                'irhpPermitType' => $row['irhpPermitRange']['irhpPermitStock']['irhpPermitType']['id']
            ]
        ];

        return vsprintf(
            '<a class="govuk-link" href="%s">%s</a>',
            [
                $this->urlHelper->fromRoute($route, $params, $options),
                Escape::html($row['permitNumber'])
            ]
        );
    }
}
