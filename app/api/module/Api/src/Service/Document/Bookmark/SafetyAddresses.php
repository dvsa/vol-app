<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * SafetyAddresses bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SafetyAddresses extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        $bundle = [
            'workshops' => [
                'contactDetails' => [
                    'person',
                    'address'
                ]
            ]
        ];
        return Qry::create(['id' => $data['licence'], 'bundle' => $bundle]);
    }

    public function render()
    {
        if (empty($this->data)) {
            return '';
        }

        $rows = [];
        $addressFormatter = new Formatter\Address();
        foreach ($this->data['workshops'] as $workshop) {
            $addressFormatter->setSeparator(', ');
            $address = $workshop['contactDetails']['address'];
            $rows[] = [
                'Address' => trim(
                    $workshop['contactDetails']['fao'] . ', ' .  $addressFormatter->format($address),
                    ', '
                ),
                'checkbox1' => $workshop['isExternal'] !== 'Y' ? 'X' : '',
                'checkbox2' => $workshop['isExternal'] === 'Y' ? 'X' : ''
            ];
        }

        $sortedRows = $this->sortSafetyAddresses($rows);
        $snippet = $this->getSnippet('SafetyAddresses');
        $parser  = $this->getParser();

        $str = '';
        foreach ($sortedRows as $tokens) {
            $str .= $parser->replace($snippet, $tokens);
        }
        return $str;
    }

    protected function sortSafetyAddresses($rows)
    {
        usort(
            $rows,
            function ($a, $b) {
                if ($a['Address'] == $b['Address']) {
                    return 0;
                } elseif ($a['Address'] < $b['Address']) {
                    return -1;
                } else {
                    return 1;
                }
            }
        );
        return $rows;
    }
}
