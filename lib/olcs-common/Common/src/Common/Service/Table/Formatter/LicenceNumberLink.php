<?php

/**
 * LicenceNumberLink.php
 */

namespace Common\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Helper\UrlHelperService;

/**
 * Class LicenceNumberLink
 *
 * Takes a licence array and creates and outputs a link for that licence.
 *
 * @package Common\Service\Table\Formatter
 */
class LicenceNumberLink implements FormatterPluginManagerInterface
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
        unset($column);

        $permittedLicenceStatuses = [
            RefData::LICENCE_STATUS_VALID,
            RefData::LICENCE_STATUS_CURTAILED,
            RefData::LICENCE_STATUS_SUSPENDED
        ];

        if (in_array($data['licence']['status'], $permittedLicenceStatuses)) {
            $url = $this->urlHelper->fromRoute('lva-licence', ['licence' => $data['licence']['id']]);

            return '<a class="govuk-link" href="' . $url . '">' . $data['licence']['licNo'] . '</a>';
        }

        return $data['licence']['licNo'];
    }
}
