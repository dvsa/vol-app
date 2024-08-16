<?php

/**
 * Created by PhpStorm.
 * User: parthvyas
 * Date: 13/08/2018
 * Time: 10:09
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

class GetDbValue extends AbstractReadonlyRepository
{
    protected $entity;

    /**
     * fetchOneEntityByX
     * @param $fetchBy
     * @param $args
     *
     * @return mixed
     * @throws \Dvsa\Olcs\Api\Domain\Exception\NotFoundException
     */
    public function fetchOneEntityByX($fetchBy, $args)
    {
        return parent::fetchOneByX($fetchBy, $args);
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
}
