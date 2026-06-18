<?php

/**
 * Year Delta
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Elements\Custom\Traits;

/**
 * Year Delta
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait YearDelta
{
    public function setOptions($options): void
    {
        parent::setOptions($options);

        // set Min/Max Year based on element's options
        $this->setMinMaxYear($this->getOption('min_year_delta'), $this->getOption('max_year_delta'));

        // This option allows us to default the date
        $defaultDate = $this->getOption('default_date');

        if ($defaultDate) {
            $dateTime = new \DateTime();

            if ($defaultDate !== 'now') {
                $dateTime->modify($defaultDate);
            }

            $this->setValue($dateTime);
        }
    }

    /**
     * Sets Min/Max Year from Delta
     *
     * @param string $minYearDelta
     * @param string $maxYearDelta
     */
    public function setMinMaxYear($minYearDelta, $maxYearDelta): void
    {
        $setMaxYear = false;
        if ($maxYearDelta) {
            $setMaxYear = true;
            $maxYear = date('Y', strtotime($maxYearDelta . ' years'));
            $this->setMaxYear($maxYear);
        }

        $setMinYear = false;
        if ($minYearDelta) {
            $setMinYear = true;
            $minYear = date('Y', strtotime($minYearDelta . ' years'));
            $this->setMinYear($minYear);
        }

        if ($setMaxYear && !$setMinYear) {
            $this->setMinYear(date('Y'));
        }
        if (!$setMinYear) {
            return;
        }
        if ($setMaxYear) {
            return;
        }
        $this->setMaxYear(date('Y'));
    }
}
