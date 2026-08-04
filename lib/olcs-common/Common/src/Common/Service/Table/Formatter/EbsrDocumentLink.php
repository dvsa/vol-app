<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;

/**
 * EBSR document link
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class EbsrDocumentLink implements FormatterPluginManagerInterface
{
    public const LINK_PATTERN = '<a class="govuk-link" href="%s">%s</a>';

    public const URL_ROUTE = 'bus-registration/ebsr';

    public const URL_ACTION = 'detail';

    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * Formats the link to an EBSR document
     *
     * @param array $data   data array
     * @param array $column column info
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $url = $this->urlHelper->fromRoute(
            self::URL_ROUTE,
            [
                'id' => $data['id'],
                'action' => self::URL_ACTION
            ]
        );

        return sprintf(self::LINK_PATTERN, $url, $data['document']['description']);
    }
}
