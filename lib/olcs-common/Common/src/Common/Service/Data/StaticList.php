<?php

namespace Common\Service\Data;

use Common\Service\Data\Interfaces\ListData;

/**
 * Class StaticList
 *
 * @package Common\Service\Data
 */
class StaticList extends AbstractDataService implements ListData
{
    /** @var array */
    protected $config;

    /**
     * Create service instance
     *
     *
     * @return StaticList
     */
    public function __construct(
        AbstractDataServiceServices $abstractDataServiceServices,
        array $config
    ) {
        parent::__construct($abstractDataServiceServices);
        $this->config = $config;
    }

    /**
     * Fetch list options
     *
     * @param string $context   Context
     * @param bool   $useGroups Use groups
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function fetchListOptions($context, $useGroups = false)
    {
        $data = $this->fetchListData($context);

        if (!$data) {
            return [];
        }

        return $data;
    }

    /**
     * Get static list data from config
     *
     * @param string $context Context
     *
     * @return array
     */
    public function fetchListData($context)
    {
        if (is_null($this->getData('static-list-' . $context))) {
            $data = $this->config['static-list-data'][$context] ?? [];
            $this->setData('static-list-' . $context, $data);
        }

        return $this->getData('static-list-' . $context);
    }
}
