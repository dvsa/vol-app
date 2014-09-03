<?php

return array(
    'documents-home' => array(
        'name' => 'documents-home',
        'attributes' => array(
            'method' => 'get',
        ),
        'elements' => array(
            'category' => array(
                'type' => 'select-noempty',
                'label' => 'documents-home.data.category',
            ),
            'subCategory' => array(
                'type' => 'select-noempty',
                'label' => 'documents-home.data.sub_category',
            ),
            'documentType' => array(
                'type' => 'select-noempty',
                'label' => 'documents-home.data.format',
            ),
            'digitalOnly' => array(
                'type' => 'checkbox',
                'label' => 'documents-home.data.digitalonly',
            )
        )
    )
);
