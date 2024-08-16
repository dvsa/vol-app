<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * PhoneNumbers bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PhoneNumbers extends DynamicBookmark
{
    /**
     * Get the Query to retrieve data
     *
     * @param array $data Known data
     *
     * @return Qry
     */
    public function getQuery(array $data)
    {
        $bundle = [
            'correspondenceCd' => [
                'phoneContacts' => [
                    'phoneContactType'
                ]
            ]
        ];
        return Qry::create(['id' => $data['licence'], 'bundle' => $bundle]);
    }

    /**
     * Render bookmark
     *
     * @return string
     */
    public function render()
    {
        if (empty($this->data)) {
            return '';
        }

        $header = [
            [
                'BOOKMARK1' => 'Phone number(s)',
                'BOOKMARK2' => ''
            ],
            [
                'BOOKMARK1' => 'Type of contact number',
                'BOOKMARK2' => 'Number'
            ]
        ];
        $numbers = [];
        if (isset($this->data['correspondenceCd']['phoneContacts'])) {
            foreach ($this->data['correspondenceCd']['phoneContacts'] as $phoneContact) {
                $numbers[] = [
                    'BOOKMARK1' => $phoneContact['phoneContactType']['description'],
                    'BOOKMARK2' => $phoneContact['phoneNumber'],
                    'type' => $phoneContact['phoneContactType']['id']
                ];
            }
            usort(
                $numbers,
                function ($a, $b) {
                    $types = [
                        PhoneContact::TYPE_PRIMARY => 1,
                        PhoneContact::TYPE_SECONDARY => 2,
                    ];
                    if ($types[$a['type']] == $types[$b['type']]) {
                        return 0;
                    } elseif ($types[$a['type']] < $types[$b['type']]) {
                        return -1;
                    } else {
                        return 1;
                    }
                }
            );
            for ($i = 0; $i < count($numbers); $i++) {
                unset($numbers[$i]['type']);
            }
        }
        $numbers = array_pad($numbers, 5, ['BOOKMARK1' => '', 'BOOKMARK2' => '']);
        $rows = array_merge($header, $numbers);

        $snippet = $this->getSnippet('CHECKLIST_2CELL_TABLE');
        $parser  = $this->getParser();

        $str = '';
        foreach ($rows as $tokens) {
            $str .= $parser->replace($snippet, $tokens);
        }
        return $str;
    }
}
