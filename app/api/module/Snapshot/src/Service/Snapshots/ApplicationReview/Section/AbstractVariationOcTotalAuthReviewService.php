<?php

/**
 * Abstract Variation Oc Total Auth Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Abstract Variation Oc Total Auth Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractVariationOcTotalAuthReviewService extends AbstractReviewService
{
    /**
     * Get the keys of the values to compare
     *
     * @param array $data
     * @return string
     */
    abstract protected function getChangedKeys($data);

    /**
     * Get total auth config
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        $config = null;

        $changes = [];

        foreach ($this->getChangedKeys($data) as $key => $translationIndex) {
            $message = $this->getValueChangedMessage($data, $key);

            if ($message !== null) {
                $changes[] = [
                    'label' => 'review-operating-centres-authorisation-' . $translationIndex,
                    'value' => $message
                ];
            }
        }

        if (!empty($changes)) {
            $config = [
                'header' => 'review-operating-centres-authorisation-title',
                'multiItems' => [
                    $changes
                ]
            ];
        }

        return $config;
    }

    /**
     * If the value has changed on the application, return a translated message
     * otherwise return null
     *
     * @param array $data
     * @param string $key
     * @return null
     */
    private function getValueChangedMessage($data, $key)
    {
        if (!isset($data[$key]) || $data[$key] == $data['licence'][$key]) {
            return null;
        }

        if ((int)$data[$key] > (int)$data['licence'][$key]) {
            $change = 'increased';
        } else {
            $change = 'decreased';
        }

        return $this->translateReplace('review-value-' . $change, [$data['licence'][$key] ?? 0, $data[$key]]);
    }
}
