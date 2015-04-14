<?php
namespace Olcs\Service\Data;

use Common\Service\Data\RefData;

/**
 * Miscellaneous Fee Type data service
 */
class MiscellaneousFeeType extends RefData
{

    /**
     * @todo update this to use refData
     */
    public function fetchListData($category = null)
    {
        return [
            ['id' => 20050, 'description' => 'Misc 1'],
            ['id' => 20051, 'description' => 'Misc 2'],
            ['id' => 20052, 'description' => 'Misc 3'],
        ];
    }
}
