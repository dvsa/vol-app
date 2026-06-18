<?php

namespace Common\Service\Table\Formatter;

use Common\Rbac\Service\Permission;
use Common\Util\Escape;

class NameActionAndStatus implements FormatterPluginManagerInterface
{
    public const BUTTON_FORMAT = '<button data-prevent-double-click="true" class="action-button-link" role="link" '
    . 'data-module="govuk-button" type="submit" name="table[action][edit][%d]">%s</button>';

    public function __construct(private Permission $permissionService)
    {
    }

    /**
     * Format a name with default edit action & associated status
     *
     * @param array $data   data row
     * @param array $column column specification
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $title = empty($data['title']['description']) ? '' : $data['title']['description'] . ' ';
        $name = Escape::html($title . $data['forename'] . ' ' . $data['familyName']);
        $newMarker = '';

        if (isset($data['status']) && ($data['status'] == 'new')) {
            $newMarker = ' <span class="overview__status green">New</span>';
        }

        if ($this->permissionService->isInternalReadOnly()) {
            return $name . $newMarker;
        }

        return sprintf(self::BUTTON_FORMAT, (int) $data['id'], $name) . $newMarker;
    }
}
