<?php

use Common\Service\Table\Formatter\Name;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'manage-users.table.title.count'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'label' => 'add-a-user',
                    'class' => 'govuk-button',
                    'id' => 'addUser'
                ],
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'Name',
            'type' => 'Action',
            'action' => 'edit',
            'formatter' => function ($row, $column) {
                $column['formatter'] = Name::class;
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                return $this->callFormatter($column, $row['contactDetails']['person']);
            }
        ],
        [
            'title' => 'email-address',
            'formatter' => fn($row) => $row['contactDetails']['emailAddress']
        ],
        [
            'title' => 'manage-users.table.column.permission.title',
            'formatter' => fn($row, $column) => implode(
                ',',
                array_map(
                    fn($role) =>
                        /**
                         * @var TableBuilder $this
                         * @psalm-scope-this TableBuilder
                         */
                        $this->translator->translate('role.' . $role['role']),
                    $row['roles']
                )
            )
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'type' => 'ActionLinks',
            'isRemoveVisible' => fn($row) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $this->permissionService->canRemoveSelfserveUser($row['id']),
            'ariaDescription' => function ($row, $column) {
                $column['formatter'] = Name::class;
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                return $this->callFormatter($column, $row['contactDetails']['person']);
            },
            'deleteInputName' => 'action[delete][%d]',
            'dontUseModal' => true,
            'actionClasses' => 'left-aligned govuk-button govuk-button--secondary'
        ],
    ]
];
