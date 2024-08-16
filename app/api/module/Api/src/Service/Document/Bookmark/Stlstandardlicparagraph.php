<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\ApplicationBundle;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\CaseBundle;
use Dvsa\Olcs\Api\Domain\Repository\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * Stlstandardlicparagraph bookmark
 */
class Stlstandardlicparagraph extends DynamicBookmark
{
    /**
     * Get the DTO query used to fetch data for this bookmark
     *
     * @param array $data Known data
     *
     * @return QueryInterface
     */
    public function getQuery(array $data)
    {
        if (isset($data['application'])) {
            return ApplicationBundle::create(['id' => $data['application'], 'bundle' => ['licenceType']]);
        }

        if (isset($data['case'])) {
            $bundle = [
                'application' => ['licenceType'],
                'licence' => ['licenceType'],
            ];
            return CaseBundle::create(['id' => $data['case'], 'bundle' => $bundle]);
        }

        // Licence must be after case, ohterwise new application, will get null for licenceType
        if (isset($data['licence'])) {
            return LicenceBundle::create(['id' => $data['licence'], 'bundle' => ['licenceType']]);
        }

        return null;
    }

    /**
     * Render bookmark
     *
     * @return null|string
     */
    public function render()
    {
        $licenceType = $this->getLicenceType();
        if (
            $licenceType === Licence::LICENCE_TYPE_STANDARD_NATIONAL
            || $licenceType === Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
        ) {
            return $this->getSnippet('Stlstandardlicparagraph/standard');
        }

        return null;
    }

    /**
     * Get the Licence type from query data
     *
     * @return string|null
     */
    private function getLicenceType()
    {
        return $this->data['licenceType']['id'] ?? $this->data['application']['licenceType']['id'] ?? $this->data['licence']['licenceType']['id'] ?? null;
    }
}
