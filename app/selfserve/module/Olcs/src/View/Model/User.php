<?php

namespace Olcs\View\Model;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\TableFactory;
use Laminas\View\Model\ViewModel;

class User extends ViewModel
{
    /**
     * Set the template for the dashboard
     *
     * @var string
     */
    protected $template = 'user';

    public function __construct(private readonly UrlHelperService $urlHelper, private readonly TableFactory $tableService)
    {
    }

    public function setUsers(array $data, array $params = []): void
    {
        $this->setVariable('users', $this->getTable('users', $data, $params));
    }

    private function getTable(
        string $table,
        array $results,
        array $data = []
    ) {
        if (!isset($data['url'])) {
            $data['url'] = $this->urlHelper;
        }

        return $this->tableService->buildTable($table, $results, $data, false);
    }
}
