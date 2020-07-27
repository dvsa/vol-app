<?php

namespace Permits\Data\Mapper;

use Common\Form\Form;
use Common\RefData;
use Permits\Controller\Config\DataSource\AvailableStocks as AvailableStocksDataSource;
use RuntimeException;

/**
 * Available stocks mapper
 */
class AvailableStocks
{
    /**
     * Map stock options
     *
     * @param array $data
     * @param Form  $form
     *
     * @return array
     */
    public function mapForFormOptions(array $data, $form)
    {
        switch ($data['type']) {
            case RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID:
                return $this->mapForEcmtShortTerm($data, $form);
            default:
                throw new RuntimeException('This mapper does not support permit type ' . $data['type']);
        }
    }

    /**
     * Map stock options for ECMT short term permit type
     *
     * @param array $data
     * @param Form  $form
     *
     * @return array
     */
    private function mapForEcmtShortTerm(array $data, $form)
    {
        $stocks = $data[AvailableStocksDataSource::DATA_KEY]['stocks'];
        $valueOptions = [];

        foreach ($stocks as $stock) {
            $valueOptions[] = [
                'value' => $stock['id'],
                'label' => $stock['periodNameKey'],
            ];
        }

        $form->get('fields')->get('stock')->setValueOptions(
            $this->transformValueOptions($valueOptions)
        );

        $suffix = (count($stocks) > 1) ? 'multiple-available' : 'one-available';

        $data['guidance'] = [
            'value' => 'permits.page.stock.guidance.' . $suffix,
            'disableHtmlEscape' => true,
        ];

        return $data;
    }

    /**
     * Set the id of the first radio button in the list for validation accessibility purposes
     *
     * @param array $valueOptions
     *
     * @return array
     */
    private function transformValueOptions(array $valueOptions)
    {
        if (count($valueOptions)) {
            $valueOptions[0]['attributes'] = [
                'id' => 'stock'
            ];
        }

        return $valueOptions;
    }
}
