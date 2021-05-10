<?php

namespace Olcs\View\Model;

use Common\View\AbstractViewModel;

/**
 * @see \OlcsTest\View\Model\LvaOverviewTest
 */
abstract class LvaOverview extends AbstractViewModel
{
    /**
     * Set the overview data
     *
     * @param array $data
     * @param array $sections
     */
    public function __construct($data, array $sections = array())
    {
        $overviewSections = [];

        $i = 1;
        foreach ($sections as $key => $section) {
            if (is_array($section)) {
                $data['sectionNumber'] = $i++;
                $overviewSections[] = $this->newSectionModel($key, $data, $section);
            } else {
                $overviewSections[] = $this->newSectionModel($section, $data);
            }
        }
        $this->setVariable('sections', $overviewSections);
    }

    /**
     * @param mixed ...$args
     * @return LvaOverviewSection
     */
    abstract protected function newSectionModel(...$args): LvaOverviewSection;
}
