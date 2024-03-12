<?php

/**
 * Licence Processing Helper
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Helper;

/**
 * Licence Processing Helper
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceProcessingHelper extends AbstractProcessingHelper
{
    /**
     * Gets navigation
     *
     * @param int $id licence ID
     * @param string $activeSection
     * @return array
     */
    public function getNavigation($id, $activeSection = null)
    {
        $sections = $this->getSections();

        $navigation = [];

        foreach (array_keys($sections) as $section) {
            $navigation[] = [
                'label' => 'internal-licence-processing-' . $section . '-label',
                'title' => 'internal-licence-processing-' . $section . '-title',
                'route' => 'licence/processing/' . $section,
                'use_route_match' => true,
                'params' => ['licence' => $id],
                'active' => $section == $activeSection
            ];
        }

        return $navigation;
    }
}
