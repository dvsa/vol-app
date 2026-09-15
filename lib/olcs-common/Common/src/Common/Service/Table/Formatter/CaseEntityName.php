<?php

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * @author Dmitry Golubev <d.e.golubev@gmail.com>
 */
class CaseEntityName implements FormatterPluginManagerInterface
{
    /**
     * Return traffic area name
     *
     * @param array $data   Data
     * @param array $column Column data
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if ($data['caseType']['id'] === \Common\RefData::CASE_TYPE_TM) {
            if (empty($data['transportManager']['homeCd']['person'])) {
                return '';
            }

            $person = $data['transportManager']['homeCd']['person'];
            $title = ($person['title'] ?? null);

            return Escape::html(
                implode(
                    ' ',
                    array_filter(
                        [
                            $title ? $title['description'] : null,
                            $person['forename'],
                            $person['familyName'],
                        ]
                    )
                )
            );
        }

        return Escape::html($data['licence']['organisation']['name']);
    }
}
