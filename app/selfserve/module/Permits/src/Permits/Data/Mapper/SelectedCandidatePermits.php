<?php

namespace Permits\Data\Mapper;

use Common\Data\Mapper\MapperInterface;

/**
 * Selected candidate permits mapper
 */
class SelectedCandidatePermits implements MapperInterface
{
    use MapFromResultTrait;

    public const CANDIDATE_PREFIX = 'candidate-';

    /**
     * @param array $data
     *
     * @return array
     */
    public function mapFromForm($data)
    {
        $candidatePermitIds = [];
        foreach ($data['fields'] as $name => $value) {
            if (str_starts_with($name, self::CANDIDATE_PREFIX) && $value == '1') {
                $candidatePermitIds[] = substr($name, strlen(self::CANDIDATE_PREFIX));
            }
        }

        $fields = $data['fields'];
        $fields['selectedCandidatePermitIds'] = $candidatePermitIds;

        return $fields;
    }
}
