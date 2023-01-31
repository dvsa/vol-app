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
    private UrlHelperService $urlHelper;
    private TableFactory $tableService;

    public function __construct(UrlHelperService $urlHelper, TableFactory $tableService)
    {
        $this->urlHelper = $urlHelper;
        $this->tableService = $tableService;
    }

    /**
     * @param array $data
     * @param array $params
     */
    public function setUsers(array $data, array $params = [])
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
