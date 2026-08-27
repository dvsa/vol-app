<?php

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * Comment formatter
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class Comment implements FormatterPluginManagerInterface
{
    /**
     * Comment value
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (isset($data[$column['name']]) && !is_null($data[$column['name']])) {
            if (
                isset($column['maxlength'])
                && is_numeric($column['maxlength'])
                && strlen($data[$column['name']]) > $column['maxlength']
            ) {
                // Escaped before the suffix is added, and before nl2br: escaping after nl2br
                // would turn the <br /> it just produced into literal text. The suffix is column
                // config rather than row data, so it stays raw.
                $content = Escape::html(mb_substr($data[$column['name']], 0, $column['maxlength']));

                if (isset($column['append'])) {
                    $content .= $column['append'];
                } else {
                    $content .= '...';
                }

                return nl2br($content);
            }

            return nl2br(Escape::html($data[$column['name']]));
        }

        return '';
    }
}
