<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;

/**
 * @package Common\Service\Table\Formatter
 *
*
 */
class SearchAddressOpposition implements FormatterPluginManagerInterface
{
    public function __construct(protected UrlHelperService $urlHelper)
    {
    }

    /**
     *
     * @param array $data   The row data.
     * @param array $column The column data.
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if ($data['opposition'] === 'Yes') {
            return sprintf(
                '<a class="govuk-link" href="%s">Yes</a>',
                $this->urlHelper->fromRoute('licence/opposition', ['licence' => $data['licId']])
            );
        }

        return 'No';
    }
}
