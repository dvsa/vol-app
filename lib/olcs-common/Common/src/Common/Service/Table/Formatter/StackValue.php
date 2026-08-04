<?php

/**
 * Stack Value formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\StackHelperService;

/**
 * Stack Value formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class StackValue implements FormatterPluginManagerInterface
{
    public function __construct(private StackHelperService $stackHelper)
    {
    }

    /**
     * Retrieve a nested value
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (!isset($column['stack'])) {
            throw new \InvalidArgumentException('No stack configuration found');
        }

        if (is_string($column['stack'])) {
            $column['stack'] = explode('->', $column['stack']);
        }

        return $this->stackHelper->getStackValue($data, $column['stack']);
    }
}
