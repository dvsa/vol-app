<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\DataHelperService;

class Address implements FormatterPluginManagerInterface
{
    protected $formats = [
        'FULL' => [
            'addressLine1',
            'addressLine2',
            'addressLine3',
            'addressLine4',
            'town',
            'postcode',
            'countryCode'
        ],
        'BRIEF' => [
            'addressLine1',
            'town',
            'postcode',
        ]
    ];

    /**
     * Format an address
     *
     * @param array $data   The row data.
     * @param array $column The column data.
     *
     * @return string                         The formatted address
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (isset($column['name'])) {
            if (strpos($column['name'], '->')) {
                $data = $this->dataHelper->fetchNestedData($data, $column['name']);
            } elseif (isset($data[$column['name']])) {
                $data = $data[$column['name']];
            }
        }

        $fields = self::getFields($column);

        $parts = [];

        $data['countryCode'] = $data['countryCode']['id'] ?? null;

        foreach ($fields as $item) {
            if (!isset($data[$item])) {
                continue;
            }
            if (empty($data[$item])) {
                continue;
            }
            $parts[] = $data[$item];
        }

        return static::formatAddress($parts);
    }

    /**
     * How to format the resulting address fields. Comma separated.
     *
     * @param string[] $parts The address fields to format
     *
     * @return string         The formatted address fields
     */
    protected static function formatAddress($parts)
    {
        return implode(', ', $parts);
    }

    /**
     * Get the list of fields to include from the column data
     *
     * @param array $column The column data.
     *
     * @return array        The fields to include
     */
    private function getFields($column)
    {
        if (isset($column['addressFields'])) {
            if (is_string($column['addressFields']) && array_key_exists($column['addressFields'], $this->formats)) {
                $fields = $this->formats[$column['addressFields']];
            } else {
                $fields = $column['addressFields'];
            }
        } else {
            $fields = [
                'addressLine1',
                'town'
            ];
        }

        return $fields;
    }

    public function __construct(private DataHelperService $dataHelper)
    {
    }
}
