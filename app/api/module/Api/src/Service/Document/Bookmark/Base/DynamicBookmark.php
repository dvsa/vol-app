<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Base;

use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareInterface;
use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareTrait;

/**
 * Dynamic bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class DynamicBookmark extends AbstractBookmark implements RepositoryManagerAwareInterface
{
    use RepositoryManagerAwareTrait;

    public const TYPE = 'dynamic';
    public const PARAM_BUSREG_ID = 'busRegId';

    protected $data = [];

    protected $params = [];

    protected $descriptions = [
        'application' => 'application',
        'licence' => 'licence',
        'user' => 'user',
        'publication' => 'publication',
        'publicationId' => 'publication',
        'opposition' => 'opposition',
        'irfoPsvAuth' => 'IRFO PSV auth',
        self::PARAM_BUSREG_ID => 'Bus Reg ID',
    ];

    /**
     * Set data used to populate the bookmark
     *
     * @param array $data Data
     *
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get the Query which can get the data required for the bookmark
     *
     * @param array $data Data
     *
     * @return \Dvsa\Olcs\Transfer\Query\AbstractQuery
     */
    abstract public function getQuery(array $data);

    /**
     * Validate data and get query
     *
     * @param array $data data
     *
     * @return \Dvsa\Olcs\Transfer\Query\AbstractQuery
     * @throws \Exception
     */
    public function validateDataAndGetQuery($data)
    {
        if (count($this->params) > 0) {
            foreach ($this->params as $param) {
                if (!isset($data[$param])) {
                    $description = $this->descriptions[$param] ?? $param;
                    throw new \Exception(
                        sprintf('Bookmark %s missing %s data', static::class, $description)
                    );
                }
            }
        }
        return $this->getQuery($data);
    }
}
