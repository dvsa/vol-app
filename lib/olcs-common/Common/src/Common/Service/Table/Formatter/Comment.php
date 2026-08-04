<?php

namespace Common\Service\Table\Formatter;

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
                $content = mb_substr($data[$column['name']], 0, $column['maxlength']);

                if (isset($column['append'])) {
                    $content .= $column['append'];
                } else {
                    $content .= '...';
                }

                return nl2br($content);
            }

            return nl2br($data[$column['name']]);
        }

        return '';
    }
}
