<?php

return array(
    'variables' => array(
        'title' => 'Tasks'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'create task' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                're-assign task' => array('requireRows' => true),
                'close task' => array('requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Link',
            'formatter' => 'TaskIdentifier',
            'name' => 'link',
            'sort' => 'linkDisplay',
        ),
        array(
            'title' => 'Category',
            'name' => 'categoryName',
            'sort' => 'categoryName',
        ),
        array(
            'title' => 'Sub category',
            'name' => 'taskSubCategoryName',
            'sort' => 'taskSubCategoryName',
        ),
        array(
            'title' => 'Description',
            'formatter' => function ($row, $column, $serviceLocator) {
                $router = $serviceLocator->get('router');
                $request = $serviceLocator->get('request');
                $routeMatch = $router->match($request);
                switch ($routeMatch->getMatchedRouteName()) {
                    case 'licence/processing/tasks':
                        $url = $this->generateUrl(
                            array(
                                'task' => $row['id'],
                                'action' => 'edit',
                                'type' => 'licence',
                                'typeId' => $row['linkId']
                            ),
                            'task_action'
                        );
                        break;
                    case 'lva-application/processing/tasks':
                        $url = $this->generateUrl(
                            array(
                                'task' => $row['id'],
                                'action' => 'edit',
                                'type' => 'application',
                                'typeId' => $row['applicationId']
                            ),
                            'task_action'
                        );
                        break;
                    case 'transport-manager/processing/tasks':
                        $url = $this->generateUrl(
                            array(
                                'task' => $row['id'],
                                'action' => 'edit',
                                'type' => 'tm',
                                'typeId' => $row['transportManagerId']
                            ),
                            'task_action'
                        );
                        break;
                    default:
                        $url = $this->generateUrl(
                            array('task' => $row['id'], 'action' => 'edit'),
                            'task_action'
                        );
                }
                return '<a href="'
                    . $url
                    . '" class=js-modal-ajax>'
                    . $row['description']
                    . '</a>';
            },
            'sort' => 'description',
        ),
        array(
            'title' => 'Date',
            'name' => 'actionDate',
            'formatter' => 'TaskDate',
            'sort' => 'actionDate',
        ),
        array(
            'title' => 'Owner',
            'formatter' => function ($row) {
                if (empty($row['ownerName'])) {
                    return 'Unassigned';
                }
                return $row['ownerName'];
            },
            'sort' => 'ownerName',
        ),
        array(
            'title' => 'Name',
            'name' => 'name',
            'sort' => 'name',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        )
    )
);
