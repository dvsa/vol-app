<?php

/**
 * Case Link
 */

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;

/**
 * Case Link
 *
 * @package Common\Service\Table\Formatter
 */
class CaseLink implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * Return a the case URL in a link format for a table.
     *
     * @param array $data
     * @param array $column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (empty($data['id'])) {
            return '';
        }

        return sprintf(
            '<a class="govuk-link" href="%s">%s</a>',
            $this->urlHelper->fromRoute(
                'case',
                [
                    'case' => $data['id']
                ]
            ),
            $data['id']
        );
    }
}
