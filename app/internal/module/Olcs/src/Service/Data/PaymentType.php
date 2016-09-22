<?php

namespace Olcs\Service\Data;

use Common\Service\Data\RefData;

/**
 * Payment Type data service
 *
 * Extends ref data and simply filters out some irrelevant choices
 * when manually paying a fee internally, as well as tweaking how
 * card choices are presented
 *
 * @package Olcs\Service\Data
 */
class PaymentType extends RefData
{
    /**
     * @var array
     */
    private $allowedTypes = [
        'fpm_cash',
        'fpm_cheque',
        'fpm_po',
        'fpm_card_offline'
    ];

    /**
     * @var array
     */
    private $overrides = [
        'fpm_card_offline' => 'Card Payment'
    ];

    /**
     * Fetch list data
     *
     * @param array $category Category
     *
     * @return array
     */
    public function fetchListData($category = null)
    {
        $category = 'fee_pay_method';
        $data = parent::fetchListData($category);

        $filtered = [];

        foreach ($data as $row) {
            if (in_array($row['id'], $this->allowedTypes)) {
                $id = $row['id'];

                if (isset($this->overrides[$id])) {
                    $row['description'] = $this->overrides[$id];
                }

                $filtered[] = $row;
            }
        }

        return $filtered;
    }
}
