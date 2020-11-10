<?php

namespace Permits\Data\Mapper;

/**
 * Selected candidate permits mapper
 */
class SelectedCandidatePermits
{
    const CANDIDATE_PREFIX = 'candidate-';

    /**
     * @param array $data
     *
     * @return array
     */
    public function mapFromForm($data)
    {
        $candidatePermitIds = [];
        foreach ($data['fields'] as $name => $value) {
            if (strpos($name, self::CANDIDATE_PREFIX) === 0 && $value == '1') {
                $candidatePermitIds[] = substr($name, strlen(self::CANDIDATE_PREFIX));
            }
        }

        $fields = $data['fields'];
        $fields['selectedCandidatePermitIds'] = $candidatePermitIds;

        return $fields;
    }
}
