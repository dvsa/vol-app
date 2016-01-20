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
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
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
