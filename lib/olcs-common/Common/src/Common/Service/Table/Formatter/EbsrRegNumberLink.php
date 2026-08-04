<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;

class EbsrRegNumberLink implements FormatterPluginManagerInterface
{
    public const LINK_PATTERN = '<a class="govuk-link" href="%s">%s</a>';

    public const URL_ROUTE = 'bus-registration/details';

    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * Formats the ebsr registration number
     *
     * @param array $data   data array
     * @param array $column column info
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        //standardise the format of the data, so this can be used by multiple tables
        //we set the data even if the busReg key is blank
        if (array_key_exists('busReg', $data)) {
            $data = $data['busReg'];
        }

        if (!isset($data['id'])) {
            return '';
        }

        $url = $this->urlHelper->fromRoute(
            self::URL_ROUTE,
            [
                'busRegId' => $data['id']
            ]
        );

        return sprintf(self::LINK_PATTERN, $url, $data['regNo']);
    }
}
