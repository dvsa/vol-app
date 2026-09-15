<?php

namespace Common\Controller\Lva\Traits;

use Common\RefData;

/**
 * Enabled Section Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait EnabledSectionTrait
{
    /**
     * Set the enabled flag
     *
     * @param array $accessibleSections
     * @param array $applicationCompletion
     * @return array
     */
    protected function setEnabledAndCompleteFlagOnSections($accessibleSections, $applicationCompletion)
    {
        $restrictionHelper = $this->restrictionHelper;
        $filter = $this->stringHelper;
        $sections = [];
        $completeSections = [];

        foreach ($applicationCompletion as $section => $status) {
            if ($status === RefData::APPLICATION_COMPLETION_STATUS_COMPLETE) {
                $section = str_replace('Status', '', $section);
                $completeSections[] = $filter->camelToUnderscore($section);
            }
        }

        foreach ($accessibleSections as $section => $settings) {
            $enabled = true;

            if (isset($settings['prerequisite'])) {
                // ignore any prerequisites that are inaccessible
                $settings['prerequisite'] = $this->removeInaccessible(
                    $settings['prerequisite'],
                    $accessibleSections
                );
            }

            if (!empty($settings['prerequisite'])) {
                $enabled = $restrictionHelper->isRestrictionSatisfied($settings['prerequisite'], $completeSections);
            }

            $complete = in_array($section, $completeSections);

            $sections[$section] = [
                'enabled'  => $enabled,
                'complete' => $complete
            ];
        }

        return $sections;
    }

    protected function removeInaccessible($prerequisites, $accessibleSections): ?array
    {
        if (is_string($prerequisites)) {
            if (!in_array($prerequisites, array_keys($accessibleSections))) {
                return null;
            }
        } elseif (is_array($prerequisites)) {
            $keep = [];
            foreach ($prerequisites as $prerequisite) {
                // recursively handle nested arrays
                if (is_array($prerequisite)) {
                    return [$this->removeInaccessible($prerequisite, $accessibleSections)];
                }

                if (in_array($prerequisite, array_keys($accessibleSections))) {
                    $keep[] = $prerequisite;
                }
            }

            return $keep;
        }

        return null;
    }
}
