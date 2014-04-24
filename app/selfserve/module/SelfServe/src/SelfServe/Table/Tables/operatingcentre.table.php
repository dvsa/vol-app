<?php

return array(
    'variables' => array(
        'title' => 'Operating centres'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Operating Centre Address',
            'formatter' => function($data) {
                $parts = array();
                foreach (array('address_line1', 'address_line2', 'address_line3', 'postcode') as $item) {
                    if (!empty($data[$item])) {
                        $parts[] = $data[$item];
                    }
                }

                return "<a href='#'>".implode(', ', $parts)."</a>";
            },
            'name' => 'address'
        ),
        array(
            'title' => 'Vehicles',
            'format' => '{{no_of_vehicles_required}}'
        ),
        array(
            'title' => 'Trailers',
            'format' => '{{no_of_trailers_required}}'
        ),
        array(
            'title' => 'Permission',
            'name' => 'permission',
            'formatter' => function($data) {
                            return ($data['permission']==1?'Y':'N');
            }
        ),
        array(
            'title' => 'Advertising',
            'name' => 'advertising',
            'formatter' => function($data) {
                            return ($data['ad_placed']==1?'Y':'N');
            }
        )
    ),
    // Footer configuration
    'footer' => array(
        array(
            'type' => 'th',
            'format' => 'Total vehicles and trailers', // i.e. 'Title: {{title}}'
            'colspan' => 2
        ),
        array(
            'formatter' => 'Sum',
            'name' => 'no_of_vehicles_required'
        ),
        array(
            'formatter' => 'Sum',
            'name' => 'no_of_trailers_required'
        ),
        array(
            'colspan' => 2
        )
    )
);
