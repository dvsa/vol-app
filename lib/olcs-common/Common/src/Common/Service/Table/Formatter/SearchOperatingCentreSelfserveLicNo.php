<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Util\Escape;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;

/**
 *
 * @package Common\Service\Table\Formatter
 *
*
 */
class SearchOperatingCentreSelfserveLicNo implements FormatterPluginManagerInterface
{
    public function __construct(private TranslatorDelegator $translator)
    {
    }

    /**
     *
     * @param array $data The row data.
     * @param array $column The column data.
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        return sprintf(
            '<a class="govuk-link" href="%s">%s</a><br/>%s',
            '/view-details/licence/' . $data['licId'],
            Escape::html($data['licNo']),
            $this->translator->translate($data['licStatusDesc'])
        );
    }
}
