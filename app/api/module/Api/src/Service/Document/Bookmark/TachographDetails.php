<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * TachographDetails bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TachographDetails extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['licence'], 'bundle' => ['tachographIns']]);
    }

    public function render()
    {
        if (empty($this->data)) {
            return '';
        }

        $checkboxes = $this->getCheckboxesBookmarks($this->data['tachographIns']['id']);
        $content = [
            'Address' => $this->data['tachographInsName'],
            'checkbox1' => $checkboxes['checkbox1'],
            'checkbox2' => $checkboxes['checkbox2'],
            'checkbox3' => $checkboxes['checkbox3']
        ];

        $snippet = $this->getSnippet('TachographDetails');
        $parser  = $this->getParser();

        return $parser->replace($snippet, $content);
    }

    protected function getCheckboxesBookmarks($tachographInsId)
    {
        return [
            'checkbox1' => ($tachographInsId == Licence::TACH_INT) ? 'X' : '',
            'checkbox2' => ($tachographInsId == Licence::TACH_EXT) ? 'X' : '',
            'checkbox3' => ($tachographInsId == Licence::TACH_NA) ? 'X' : ''
        ];
    }
}
