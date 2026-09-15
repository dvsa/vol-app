<?php

namespace Common\Service;

/**
 * Class Cases
 * @package Common\Service
 */
class Cases
{
    public const CASE_CATEGORY_NR = 'case_cat_compl_erru_msi';

    public const NR_CATEGORY_DEFAULT = 'erru_case_t_msirnys';
     //MSI with no response sent
    public const NR_DEFAULT_INFRINGEMENT_CATEGORY = 'MSI'; //currently the only one we have

    /**
     * @param int[] $data
     *
     * @return array
     *
     * @psalm-param array{case: 29} $data
     */
    public function createNrCase($data)
    {
        $data['erruCaseType'] = self::NR_CATEGORY_DEFAULT;
        $data['caseType'] = self::CASE_CATEGORY_NR;
        $data['openDate'] = date('Y-m-d H:i:s');
        $data['seriousInfringements'][0]['siCategory'] = self::NR_DEFAULT_INFRINGEMENT_CATEGORY;
        $data['_OPTIONS_'] = $this->getNrCascadeOptions();

        return $data;
    }

    /**
     * Cascade options when creating Nr case
     *
     * @return array
     */
    public function getNrCascadeOptions()
    {
        return [
            'cascade' => [
                'list' => [
                    'seriousInfringements' => [
                        'entity' => 'SeriousInfringement',
                        'parent' => 'case',
                    ],
                ]
            ]
        ];
    }
}
