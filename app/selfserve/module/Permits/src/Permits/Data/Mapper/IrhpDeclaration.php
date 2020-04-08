<?php

namespace Permits\Data\Mapper;

use Common\RefData;

/**
 * Declaration mapper
 */
class IrhpDeclaration
{
    const BILATERAL_DECLARATION_LABEL = 'permits.form.declaration.label.bilateral';

    /**
     * Map for form options
     *
     * @param array $data
     * @param mixed $form
     *
     * @return array
     */
    public function mapForFormOptions(array $data, $form): array
    {
        if ($data['type'] == RefData::IRHP_BILATERAL_PERMIT_TYPE_ID) {
            $form->get('fields')->get('declaration')->setLabel(self::BILATERAL_DECLARATION_LABEL);
        }
        return $data;
    }
}
