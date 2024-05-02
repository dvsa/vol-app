<?php

namespace Olcs\Service\Marker;

use Common\RefData;

/**
 * LicenceStatusRuleMarker
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class LicenceStatusRuleMarker extends AbstractMarker
{
    public function canRender()
    {
        $data = $this->getData();
        if (!isset($data['licence']['status'])) {
            return false;
        }
        if ($data['licence']['status']['id'] !== RefData::LICENCE_STATUS_VALID) {
            return false;
        }

        $rules = $this->getCurrentRules($data['licence']);

        return (count($rules) > 0);
    }

    public function render()
    {
        $data = $this->getData();
        $rules = $this->getCurrentRules($data['licence']);
        $startDateTime = new \DateTime($rules[0]['startDate']);
        $endDateTime = ($rules[0]['endDate']) ? new \DateTime($rules[0]['endDate']) : null;

        return $this->renderPartial(
            'licence-status-rule',
            [
                'startDateTime' => $startDateTime,
                'endDateTime' => $endDateTime,
                'status' => $rules[0]['licenceStatus'],
                'ruleId' => $rules[0]['id'],
                'licenceId' => $data['licence']['id'],
            ]
        );
    }

    /**
     * Get a list of current LicenceStatusRule data
     *
     *
     * @return array
     */
    private function getCurrentRules(array $licence)
    {
        $rules = [];
        $statuses = [
            RefData::LICENCE_STATUS_SUSPENDED,
            RefData::LICENCE_STATUS_REVOKED,
            RefData::LICENCE_STATUS_CURTAILED,
        ];

        foreach ($licence['licenceStatusRules'] as $rule) {
            // if already expired
            if (!empty($rule['endProcessedDate'])) {
                continue;
            }
            // if already processed
            if (!empty($rule['startProcessedDate']) && empty($rule['endProcessedDate'])) {
                continue;
            }
            // if its not one of the statuses then weere not interested in it
            if (!in_array($rule['licenceStatus']['id'], $statuses)) {
                continue;
            }
            $rules[] = $rule;
        }

        return $rules;
    }
}
