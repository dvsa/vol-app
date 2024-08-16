<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * LicencePartners bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicencePartners extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        $bundle = [
            'organisation' => [
                'organisationPersons' => [
                    'person'
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

        $rows[] = [
            'BOOKMARK1' => 'List of partners/directors (please enter full name if not shown)',
            'BOOKMARK2' => 'Date of birth (please complete if not shown)'
        ];
        foreach ($this->data['organisation']['organisationPersons'] as $orgPerson) {
            $person = $orgPerson['person'];
            $birthDate = new \DateTime($person['birthDate']);
            $rows[] = [
                'BOOKMARK1' => $person['forename'] . ' ' . $person['familyName'],
                'BOOKMARK2' => $birthDate->format('d/m/Y')
            ];
        }

        $rows = array_pad($rows, 4, ['BOOKMARK1' => '', 'BOOKMARK2' => '']);

        $snippet = $this->getSnippet('CHECKLIST_2CELL_TABLE');
        $parser  = $this->getParser();

        $str = '';
        foreach ($rows as $tokens) {
            $str .= $parser->replace($snippet, $tokens);
        }
        return $str;
    }
}
