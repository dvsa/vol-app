<?php

/**
 * PI Report Record formatter
 */

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;

/**
 * PI Report Record formatter
 */
class PiReportRecord implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * Format a PI Report Record
     *
     * @param array $data
     * @param array $column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (!empty($data['pi']['case']['licence'])) {
            return sprintf(
                '<a class="govuk-link" href="%s">%s</a> (%s)',
                $this->urlHelper->fromRoute(
                    'licence',
                    [
                        'licence' => $data['pi']['case']['licence']['id']
                    ]
                ),
                $data['pi']['case']['licence']['licNo'],
                $data['pi']['case']['licence']['status']['description']
            );
        }
        if (!empty($data['pi']['case']['transportManager'])) {
            return sprintf(
                '<a class="govuk-link" href="%s">TM %s</a> (%s)',
                $this->urlHelper->fromRoute(
                    'transport-manager/details',
                    [
                        'transportManager' => $data['pi']['case']['transportManager']['id']
                    ]
                ),
                $data['pi']['case']['transportManager']['id'],
                $data['pi']['case']['transportManager']['tmStatus']['description']
            );
        }

        return '';
    }
}
