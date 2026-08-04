<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;

/**
 * Class AccessedCorrespondence
 *
 * Accessed correspondence formatter, displays correspondence as a link to the document and
 * denotes whether the correspondence has been accessed.
 *
 * @package Common\Service\Table\Formatter
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class AccessedCorrespondence implements FormatterPluginManagerInterface
{
    public function __construct(protected UrlHelperService $urlHelper, protected TranslatorDelegator $translator)
    {
    }

    /**
     * Get a link for the document with the access indicator.
     *
     * @param array $data   The row data.
     * @param array $column The column data.
     *
     * @return string The document link and accessed indicator
     */
    #[\Override]
    public function format($data, $column = [])
    {

        $url = $this->urlHelper->fromRoute(
            'correspondence/access',
            [
                'correspondenceId' => $data['correspondence']['id'],
            ]
        );

        $title = '';
        if ($data['correspondence']['accessed'] === 'N') {
            $title .= '<span class="status green">' .
                $this->translator->translate('dashboard-correspondence.table.status.new') .
                '</span> ';
        }

        $extension = pathinfo($data['correspondence']['document']['filename'], PATHINFO_EXTENSION);
        if (!empty($extension)) {
            $extension = ' (' . $extension . ')';
        }

        return sprintf(
            '<a class="govuk-link" href="%s" target="_blank"><b>%s%s</b></a>%s',
            $url,
            $data['correspondence']['document']['description'],
            $extension,
            $title
        );
    }
}
