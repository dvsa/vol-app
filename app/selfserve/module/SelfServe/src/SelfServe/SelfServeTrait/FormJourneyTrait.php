<?php

namespace SelfServe\SelfServeTrait;

trait FormJourneyTrait
{
    /**
     * Get licence entity based on route id value
     *
     * @return array|false
     */
    private function _getLicenceEntity()
    {
        $licenceId = (int) $this->params()->fromRoute('licenceId');
        return $this->makeRestCall('Licence', 'GET', array('id' => $licenceId));
    }
}