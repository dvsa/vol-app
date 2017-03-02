<?php

return array(
    'variables' => array(
        'title' => 'Serious Infringements'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--primary'),
                'edit' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50, 100)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'Id',
            'formatter' => function ($data) {
                return sprintf(
                    '<a href="%s" class="js-modal-ajax">%s</a>',
                    $this->generateUrl(array('action' => 'edit', 'id' => $data['id']), 'case_penalty'),
                    $data['id']
                );
            }
        ),
        array(
            'title' => 'Opposition type',
            'formatter' => 'RefData',
            'name' => 'siCategoryType'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        )
    )
);
