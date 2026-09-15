<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Util\Escape;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;

/**
 * @package Common\Service\Table\Formatter
 *
*
 */
class SearchAddressOperatorName implements FormatterPluginManagerInterface
{
    public function __construct(protected UrlHelperService $urlHelper)
    {
    }

    /**

     *
     * @param array $data   The row data.
     * @param array $column The column data.
     *
     * @return string The document link and accessed indicator
     */
    #[\Override]
    public function format($data, $column = [])
    {
        return sprintf(
            '<a class="govuk-link" href="%s">%s</a>',
            $this->urlHelper->fromRoute('operator/business-details', ['organisation' => $data['orgId']]),
            Escape::html($data['orgName'])
        );
    }
}
