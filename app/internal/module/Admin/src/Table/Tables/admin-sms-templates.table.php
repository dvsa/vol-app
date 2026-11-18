<?php

/**
 * SMS templates (VOL-7542): one row per template name. Same shape as the email group list.
 */

return [
    'variables' => [
        'title' => 'SMS Templates',
        'titleSingular' => 'SMS Templates',
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ]
        ],
        'crud' => [
            'actions' => [
                'edit' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary js-require--one'
                ]
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Description',
            'name' => 'description',
        ],
        [
            'title' => 'Languages',
            'name' => 'locales',
            'formatter' => fn($row) => implode(
                ' ',
                array_map(
                    static fn($locale) => sprintf(
                        '<strong class="govuk-tag govuk-tag--turquoise">%s</strong>',
                        htmlspecialchars(match ($locale) {
                            'en_GB' => 'EN',
                            'cy_GB' => 'CY',
                            default => (string) $locale,
                        }, ENT_QUOTES, 'UTF-8'),
                    ),
                    is_array($row['locales'] ?? null) ? $row['locales'] : [],
                ),
            ),
        ],
        [
            'title' => 'markup-table-th-action',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
