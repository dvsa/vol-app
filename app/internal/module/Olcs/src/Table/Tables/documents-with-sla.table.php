<?php

use Common\Service\Table\Formatter\DocumentDescription;
use Common\Service\Table\Formatter\DocumentSubcategory;
use Common\Service\Table\Formatter\FileExtension;
use Common\Service\Table\Formatter\SlaTargetDate;

return array(
    'variables' => array(
        'title' => 'Docs & attachments'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'upload' => array('class' => 'govuk-button'),
                'New letter' => array(),
                'delete' => array('class' => 'govuk-button govuk-button--warning js-require--multiple', 'requireRows' => true),
                'split' => array('class' => 'govuk-button govuk-button--secondary', 'requireRows' => true),
                'relink' => array('class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'options' => array(10, 25, 50)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description',
            'formatter' => DocumentDescription::class,
        ),
        array(
            'title' => 'Category',
            'name' => 'categoryName',
            'sort' => 'categoryName'
        ),
        array(
            'title' => 'Subcategory',
            'name' => 'documentSubCategoryName',
            'sort' => 'documentSubCategoryName',
            'formatter' => DocumentSubcategory::class
        ),
        array(
            'title' => 'Format',
            'formatter' => FileExtension::class
        ),
        array(
            'title' => 'Date',
            'name' => 'issuedDate',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
            'sort' => 'issuedDate',
        ),
        array(
            'title' => 'SLA target date',
            'name' => 'slaTargetDate',
            'formatter' => SlaTargetDate::class
        ),
        array(
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'data-attributes' => array(
                'filename'
            )
        )
    )
);
