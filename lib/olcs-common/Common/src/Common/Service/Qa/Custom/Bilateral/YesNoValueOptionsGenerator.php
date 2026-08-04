<?php

namespace Common\Service\Qa\Custom\Bilateral;

class YesNoValueOptionsGenerator
{
    /**
     * Generate an array of value options for a yes/no radio element
     *
     * @param string $yesCaption
     * @param string $noCaption
     *
     * @return array
     */
    public function generate($yesCaption, $noCaption)
    {
        return [
            'yes' => [
                'label' => $yesCaption,
                'value' => 'Y',
                'attributes' => [
                    'id' => 'yesContent',
                ]
            ],
            'no' => [
                'label' => $noCaption,
                'value' => 'N',
            ]
        ];
    }
}
