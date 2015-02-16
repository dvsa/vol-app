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
                $router     = $serviceLocator->get('router');
                $request    = $serviceLocator->get('request');
                $routeMatch = $router->match($request);
                $params     = $routeMatch->getParams();

                // the edit URL should pass the context of the page we're on,
                // rather than the type of each task
                // (see https://jira.i-env.net/browse/OLCS-6041)
                switch ($routeMatch->getMatchedRouteName()) {
                    case 'licence/processing/tasks':
                        $routeParams = ['type' => 'licence', 'typeId' => $params['licence']];
                        break;
                    case 'lva-application/processing/tasks':
                        $routeParams = ['type' => 'application', 'typeId' => $params['application']];
                        break;
                    case 'transport-manager/processing/tasks':
                        $routeParams = ['type' => 'tm', 'typeId' => $params['transportManager']];
                        break;
                    case 'licence/bus-processing/tasks':
                        $routeParams = [
                            'type'    => 'busreg',
                            'typeId'  => $params['busRegId'],
                            'licence' => $params['licence']
                        ];
                        break;
                    case 'case_processing_tasks':
                        $routeParams = ['type' => 'case', 'typeId' => $params['case']];
                        break;
                    default:
                        $routeParams = [];
                        break;
                }
                $url = $this->generateUrl(
                    array_merge(
                        [
                            'task' => $row['id'],
                            'action' => 'edit',
                        ],
                        $routeParams
                    ),
                    'task_action'
                );
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
