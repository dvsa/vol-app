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
                'type' => 'select-noempty',
                'label' => 'documents-home.data.digitalonly',
                'value_options' => 'document_types'
            ),
            'submit' => array(
                'enable' => true,
                'type' => 'submit',
                'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                'label' => 'documents-home.submit.filter',
                'class' => 'action--primary large'
            )
        )
    )
);
