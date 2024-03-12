<?php

namespace Olcs\Service\Marker;

use Common\RefData;

/**
 * LicenceStatusMarker
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class LicenceStatusMarker extends AbstractMarker
{
    public function canRender()
    {
        $data = $this->getData();

        if (!isset($data['licence']['status'])) {
            return false;
        }

        $statuses = [
            RefData::LICENCE_STATUS_SUSPENDED,
            RefData::LICENCE_STATUS_REVOKED,
            RefData::LICENCE_STATUS_CURTAILED,
        ];

        return in_array($data['licence']['status']['id'], $statuses);
    }

    public function render()
    {
        $startDateTime = null;
        $endDateTime = null;
        $data = $this->getData();
        $activeRule = $this->getActiveRule($data['licence']);

        if ($activeRule) {
            $startDateTime = new \DateTime($activeRule['startDate']);
            $endDateTime = (!empty($activeRule['endDate'])) ? new \DateTime($activeRule['endDate']) : null;
        } else {
            $dateName = null;
            switch ($data['licence']['status']['id']) {
                case RefData::LICENCE_STATUS_CURTAILED:
                    $dateName = 'curtailedDate';
                    break;
                case RefData::LICENCE_STATUS_REVOKED:
                    $dateName = 'revokedDate';
                    break;
                case RefData::LICENCE_STATUS_SUSPENDED:
                    $dateName = 'suspendedDate';
                    break;
            }

            if (isset($data['licence'][$dateName]) && !empty($data['licence'][$dateName])) {
                $startDateTime = new \DateTime($data['licence'][$dateName]);
            }
        }

        return $this->renderPartial(
            'licence-status',
            [
                'startDateTime' => $startDateTime,
                'endDateTime' => $endDateTime,
                'status' => $data['licence']['status'],
            ]
        );
    }

    /**
     * Get the active LicenceStatusRule data
     *
     * @param array $licence
     *
     * @return array|boolean
     */
    private function getActiveRule(array $licence)
    {
        $rules = [];
        foreach ($licence['licenceStatusRules'] as $rule) {
            // if already expired
            if (!empty($rule['endProcessedDate'])) {
                continue;
            }
            // if rule status is not same as licence status, then this can't be the active rule
            if ($rule['licenceStatus'] != $licence['status']) {
                continue;
            }

            $rules[] = $rule;
        }
        if (count($rules) > 0) {
            usort(
                $rules,
                fn($a, $b) => strtotime($b['startDate']) - strtotime($a['startDate'])
            );
            return $rules[0];
        }

        return false;
    }
}
