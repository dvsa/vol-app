<?php

namespace Common\Data\Object\Search;

use Common\Service\Helper\UrlHelperService as UrlHelper;
use Common\Util\Escape;

/**
 * Class SearchAbstract
 * @package Common\Data\Object\Search
 */
abstract class SearchAbstract
{
    /**
     * @var
     */
    protected $title;

    /**
     * @var
     */
    protected $key;

    /**
     * @var
     */
    protected $searchIndices;

    /**
     * @var string
     */
    protected $displayGroup = 'all';

    /**
     * Order/sorting options
     * Contains an array of required results order. This will generate a Select list with value of <field>-<order> and
     * against the label. Then gets split on '-' into sort and order params for the backend query.
     *
     * E.g. [
     *   0 => [
     *       'field' => 'pub_date',
     *       'field_label' => 'Most recent publication',
     *       'order' => 'desc'
     *       ]
     * ]
     * @var array
     */
    protected $orderOptions = [];

    /**
     * Get searchIndices
     *
     * @return mixed
     */
    public function getSearchIndices()
    {
        return $this->searchIndices;
    }

    /**
     * get key
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * get title
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * get variables
     *
     * @return array
     */
    public function getVariables()
    {
        return ['title' => $this->getTitle(), 'empty_message' => 'search-no-results-internal'];
    }

    /**
     * get settings
     *
     * @return array
     */
    public function getSettings()
    {
        return [
            'paginate' => [
                'limit' => [
                    'options' => [10, 25, 50]
                ]
            ]
        ];
    }

    /**
     * get attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return [];
    }

    /**
     * get date ranges
     *
     * @return array
     */
    public function getDateRanges()
    {
        return [];
    }

    /**
     * get filters
     *
     * @return array
     */
    public function getFilters()
    {
        return [];
    }

    /**
     * get order options
     *
     * @return array
     */
    public function getOrderOptions()
    {
        return $this->orderOptions;
    }

    /**
     * get columns
     *
     * @return array
     */
    abstract public function getColumns();

    /**
     * get table config
     *
     * @return array
     */
    public function getTableConfig()
    {
        return [
            'variables' => $this->getVariables(),
            'settings' => $this->getSettings(),
            'attributes' => $this->getAttributes(),
            'columns' => $this->getColumns()
        ];
    }

    /**
     * get navigation
     *
     * @param array $queryParams query parameters
     *
     * @return array
     */
    public function getNavigation(array $queryParams = [])
    {
        return [
            'id' => 'search-' . $this->getKey(),
            'label' => $this->getTitle(),
            'route' => 'search',
            'params' => ['index' => $this->getKey(), 'action' => 'reset'],
            'query' => $queryParams,
            'class' => 'govuk-link',
        ];
    }

    /**
     * get display group
     *
     * @return string
     */
    public function getDisplayGroup()
    {
        return $this->displayGroup;
    }

    /**
     * Formats a cell with a licence link based on licNo
     *
     * @param array     $row        data row
     * @param UrlHelper $urlHelper  url helper
     * @param bool      $showAsText Whether to return text only
     *
     * @return string
     */
    public function formatCellLicNo($row, $urlHelper, $showAsText = false)
    {
        if ($showAsText) {
            return Escape::html($row['licNo']);
        }

        return sprintf(
            '<a class="govuk-link" href="%s">%s</a>',
            $urlHelper->fromRoute('licence-no', ['licNo' => trim($row['licNo'])]),
            Escape::html($row['licNo'])
        );
    }
}
