<?php
namespace Olcs\Service\Data;

use Common\Service\Data\RefData;

class PaymentType extends RefData
{
    private $allowedTypes = [
        'fpm_cash',
        'fpm_cheque',
        'fpm_po',
        'fpm_card_offline'
    ];

    private $overrides = [
        'fpm_card_offline' => 'Card Payment'
    ];

    public function fetchListData($category)
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
