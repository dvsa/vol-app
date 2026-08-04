<?php

/**
 * ConvictionDescription formatter
 * If category is set against a conviction, use the category description text as the description.
 * If the category is NOT SET OR is set to User defined (new entries only) then this implies a user defined
 * category and the category.category_text field should be used as the description.
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\RefData;

/**
 * ConvictionDescription formatter
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ConvictionDescription implements FormatterPluginManagerInterface
{
    /**
     * ConvictionDescription value
     *
     * conv_c_cat_1144 is ref data id for a 'User defined' category. However all new convictions with
     * User defined descriptions have no category. Hence the need for this formatter.
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (
            isset($data['convictionCategory']['id'])
            && $data['convictionCategory']['id'] !== RefData::CONVICTION_CATEGORY_USER_DEFINED
        ) {
            $data['categoryText'] = $data['convictionCategory']['description'];
        }

        $categoryText = $data['categoryText'];

        $append = strlen($categoryText) > 30 ? '...' : '';
        return nl2br(substr($categoryText, 0, 30)) . $append;
    }
}
