<?php

namespace Permits\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Common\Exception\ResourceNotFoundException;

class PermitTypeTitle implements MapperInterface
{
    use MapFromResultTrait;

    /**
     * Maps data
     *
     * @param array $data Array of data retrieved from the backend
     *
     * @return array
     *
     */
    public function mapForDisplay(array $data)
    {
        $data['prependTitle'] = $data['irhpPermitType']['name']['description'];
        return $data;
    }
}
