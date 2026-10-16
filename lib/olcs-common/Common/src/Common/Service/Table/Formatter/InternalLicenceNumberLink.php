<?php

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;
use Common\Service\Helper\UrlHelperService;

/**
 * Class LicenceNumberLink
 *
 * Takes a licence array and creates and outputs a link for that licence.
 *
 * @package Common\Service\Table\Formatter
 */
class InternalLicenceNumberLink implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * Return a the licence URL in a link format for a table.
     *
     * @param array $data   The row data.
     * @param array $column The column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $licenceNo = $data['licence']['licNo'];
        $url = $this->urlHelper->fromRoute('lva-licence', ['licence' => $data['licence']['id']]);

        // Interpolated twice — once into the title attribute, once as the link text.
        $escapedLicenceNo = Escape::html($licenceNo);

        return '<a class="govuk-link" href="' . $url . '" title="Licence details for ' . $escapedLicenceNo . '">'
            . $escapedLicenceNo . '</a>';
    }
}
