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
            'documentSubCategory' => array(
                'type' => 'select-noempty',
                'label' => 'documents-home.data.sub_category',
            ),
            'fileExtension' => array(
                'type' => 'select-noempty',
                'label' => 'documents-home.data.format',
            ),
            'isDigital' => array(
                'type' => 'checkbox',
                'label' => 'documents-home.data.digitalonly',
            )
        )
    )
);
