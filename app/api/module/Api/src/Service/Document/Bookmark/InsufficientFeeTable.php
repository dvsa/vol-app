<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\FeeBundle as Qry;

/**
 * InsufficientFeeTable bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InsufficientFeeTable extends DynamicBookmark
{
    public const RTF_BOLD_START = '{\b ';
    public const RTF_BOLD_END = '}';

    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['fee']]);
    }

    public function render()
    {
        if (empty($this->data)) {
            return '';
        }

        $rows = [];
        $rows[] = [
            'COL1_BMK1' => self::RTF_BOLD_START . 'Fee description' . self::RTF_BOLD_END,
            'COL2_BMK2' => self::RTF_BOLD_START . 'Amount' . self::RTF_BOLD_END,
        ];

        $rows[] = [
            'COL1_BMK1' => $this->data['description'],
            'COL2_BMK1' => "\'a3",
            'COL2_BMK2' => number_format($this->data['amount'], 2)
        ];

        $rows[] = [
            'COL1_BMK1' => 'Amount RECEIVED',
            'COL2_BMK1' => "\'a3",
            'COL2_BMK2' => number_format($this->data['receivedAmount'], 2)
        ];

        $rows[] = [
            'COL1_BMK1' => self::RTF_BOLD_START . 'BALANCE NOW DUE' . self::RTF_BOLD_END,
            'COL2_BMK1' => self::RTF_BOLD_START . "\'a3" . self::RTF_BOLD_END,
            'COL2_BMK2' => self::RTF_BOLD_START .
                number_format($this->data['outstandingAmount'], 2) . self::RTF_BOLD_END
        ];

        $snippet = $this->getSnippet('TABLE_INSUFFICIENT_FEE');
        $parser  = $this->getParser();

        $str = '';
        foreach ($rows as $tokens) {
            $str .= $parser->replace($snippet, $tokens);
        }
        return $str;
    }
}
