<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\StackHelperService;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;

/**
 * YesNo formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class YesNo implements FormatterPluginManagerInterface
{
    public function __construct(private StackHelperService $stackHelper, private TranslatorDelegator $translator)
    {
    }

    /**
     * Format a address
     *
     * @param array $data   Data
     * @param array $column Column parameterd
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (isset($column['stack'])) {
            if (is_string($column['stack'])) {
                $column['stack'] = explode('->', $column['stack']);
            }

            $value = $this->stackHelper->getStackValue($data, $column['stack']);
        } else {
            $value = $data[$column['name']];
        }

        return $this->translator->translate(
            $value !== 'N' && !empty($value)
            ? 'common.table.Yes'
            : 'common.table.No'
        );
    }
}
