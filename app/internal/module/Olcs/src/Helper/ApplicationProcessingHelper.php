<?php

/**
 * Application Processing Helper
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Helper;

/**
 * Application Processing Helper
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationProcessingHelper extends AbstractProcessingHelper
{
    /**
     * Gets navigation
     *
     * @param int $id application ID
     * @param string $activeSection
     * @return array
     */
    public function getNavigation($id, $activeSection = null)
    {
        $sections = $this->getSections();

        $navigation = [];

        foreach (array_keys($sections) as $section) {
            $navigation[] = [
                'label' => 'internal-application-processing-' . $section . '-label',
                'title' => 'internal-application-processing-' . $section . '-title',
                'route' => 'lva-application/processing/' . $section,
                'use_route_match' => true,
                'params' => ['application' => $id],
                'active' => $section == $activeSection
            ];
        }

        return $navigation;
    }
}
