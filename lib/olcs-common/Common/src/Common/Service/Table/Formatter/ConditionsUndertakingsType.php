<?php

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;

/**
 * ConditionsUndertakingsType
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ConditionsUndertakingsType implements FormatterPluginManagerInterface
{
    public function __construct(private TranslatorDelegator $translator)
    {
    }

    /**
     * Get the condition undertaking type and add Schedule 4/1 text if applicable
     *
     * @param array $data   The row data.
     * @param array $column The column data.
     *
     * @return mixed
     */
    #[\Override]
    public function format($data, $column = [])
    {
        // supress PMD warning
        unset($column);

        $content = Escape::html($data['conditionType']['description']);

        if ($data['s4'] !== null) {
            $content .= '<br>' . $this->translator->translate('(Schedule 4/1)');
        }

        return $content;
    }
}
