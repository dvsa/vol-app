<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Util\Escape;

/**
 * IRHP Permit Application Ref Link formatter
 */
class IrhpPermitApplicationRefLink implements FormatterPluginManagerInterface
{
    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * Format
     *
     * Returns the IRHP Permit Application Ref Link
     *
     * @param array $data   Row data
     * @param array $column Column Parameters
     *
     * @return                                        string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function format($data, $column = [])
    {
        return isset($data['irhpPermitApplication']['relatedApplication']) ? sprintf(
            '<a class="govuk-link" href="%s">%s</a>',
            $this->urlHelper->fromRoute(
                'licence/irhp-application',
                [
                    'action' => 'index',
                    'licence' => $data['irhpPermitApplication']['relatedApplication']['licence']['id']
                ]
            ),
            Escape::html($data['irhpPermitApplication']['relatedApplication']['applicationRef'])
        ) : '';
    }
}
