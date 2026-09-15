<?php

namespace Common\Service\Table\Formatter;

/**
 * TaskAllocationRuleCriteria
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TaskAllocationCriteria implements FormatterPluginManagerInterface
{
    /**
     * Format Criteria string
     *
     * @param  array $data
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $content = 'N/A';
        if (isset($data['goodsOrPsv']['id'])) {
            if ($data['goodsOrPsv']['id'] === \Common\RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
                $content = ($data['isMlh'] === true) ? 'Goods, MLH' : 'Goods, Non-MLH';
            } elseif ($data['goodsOrPsv']['id'] === \Common\RefData::LICENCE_CATEGORY_PSV) {
                $content = 'PSV';
            }
        }

        return $content;
    }
}
