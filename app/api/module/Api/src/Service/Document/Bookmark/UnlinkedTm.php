<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Unlinked Tm
 *
 * Returns all the transport managers for and family names.
 *
 * @author Josh Curtis <josh.curtis@valtech.com>
 */
class UnlinkedTm extends DynamicBookmark
{
    public const TM_NA = 'N/A';
    public const TM_BE_NOMINATED = 'To be nominated.';

    /**
     * Get the query, this query returns the licences transport managers contact
     * details.
     *
     * @param array $data The licence data
     *
     * @return \Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle
     */
    public function getQuery(array $data)
    {
        $bundle = [
            'tmLicences' => [
                'transportManager' => [
                    'homeCd' => [
                        'person'
                    ]
                ]
            ]
        ];

        return Qry::create(['id' => $data['licence'], 'bundle' => $bundle]);
    }

    /**
     * Return the TM's fore and surnames.
     *
     * @return string The TM fore and family names.
     */
    public function render()
    {
        $licences = $this->data['tmLicences'];

        if (count($licences) === 0) {
            if ($this->data['licenceType']['id'] === Licence::LICENCE_TYPE_RESTRICTED) {
                return self::TM_NA;
            }

            return self::TM_BE_NOMINATED;
        }

        $output = [];
        foreach ($licences as $licence) {
            $person = $licence['transportManager']['homeCd']['person'];
            $output[] = Formatter\Name::format($person);
        }

        return implode("\n", $output);
    }
}
