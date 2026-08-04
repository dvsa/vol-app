<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;

/**
 * TransportManagerName Formatter
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagerName extends Name
{
    public function __construct(private UrlHelperService $urlHelper, private TranslatorDelegator $translator)
    {
    }

    /**
     * Format
     *
     * @param array $data   Row Data
     * @param array $column Col params
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $name = parent::format($data['name'], $column);

        if (!isset($column['internal']) || (!isset($column['lva']))) {
            return $name;
        }

        // default
        $html = $name;
        if ($column['internal']) {
            switch ($column['lva']) {
                case 'application':
                case 'licence':
                    $html = sprintf(
                        '<a class="govuk-link" href="%s">%s</a>',
                        static::getInternalUrl($data),
                        $name
                    );
                    break;
                case 'variation':
                    $html = sprintf(
                        '%s <a class="govuk-link" href="%s">%s</a>',
                        static::getActionName($data),
                        static::getInternalUrl($data),
                        $name
                    );
                    break;
            }
        } else {
            // External
            switch ($column['lva']) {
                case 'licence':
                    $html = $name;
                    break;
                case 'variation':
                    // only hyperlink if Added or Updated
                    if (isset($data['action']) && ($data['action'] == 'A' || $data['action'] == 'U')) {
                        $html = sprintf(
                            '%s <a class="govuk-link" href="%s">%s</a>',
                            static::getActionName($data),
                            static::getExternalUrl($data, $column['lva']),
                            $name
                        );
                    } else {
                        $html = sprintf(
                            '%s %s',
                            static::getActionName($data),
                            $name
                        );
                    }

                    break;
                case 'application':
                    $html = sprintf(
                        '<a class="govuk-link" href="%s">%s</a>',
                        static::getExternalUrl($data, $column['lva']),
                        $name
                    );
                    break;
            }
        }

        return $html;
    }

    /**
     * Get URL for the Transport Managers name
     *
     * @param array  $data Row Data
     * @param string $lva  Type (Lic|Var|App)
     *
     * @return string
     */
    protected function getExternalUrl($data, $lva)
    {
        $route = 'lva-' . $lva . '/transport_manager_details';

        return $this->urlHelper->fromRoute($route, ['action' => null, 'child_id' => $data['id']], [], true);
    }

    /**
     * Get URL for the Transport Managers name
     *
     * @param array $data Row Data
     *
     * @return string
     */
    protected function getInternalUrl($data)
    {
        $transportManagerId = $data['transportManager']['id'];

        return $this->urlHelper->fromRoute(
            'transport-manager/details',
            ['transportManager' => $transportManagerId],
            [],
            true
        );
    }

    /**
     * Convert action eg "U" into its description
     *
     * @param string $data Row Data
     *
     * @return string Description
     */
    protected function getActionName($data)
    {
        $statusMaps = [
            'U' => 'tm_application.table.status.updated',
            'D' => 'tm_application.table.status.removed',
            'A' => 'tm_application.table.status.new',
            'C' => 'tm_application.table.status.current',
        ];

        if (!isset($data['action']) || !isset($statusMaps[$data['action']])) {
            return '';
        }

        return $this->translator->translate($statusMaps[$data['action']]);
    }
}
