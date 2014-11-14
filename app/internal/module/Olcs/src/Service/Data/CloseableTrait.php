<?php

/**
 * Closeable Trait
 */
namespace Olcs\Service\Data;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Close Entity Trait - contains methods to update the closedField of an entity
 */
trait CloseableTrait
{
    public function closeEntity($id)
    {
        $data = $this->fetchData($id);
        $now = date('Y-m-d h:i:s');

        $this->getRestClient()->update(
            $data['id'],
            [
                'data' => json_encode(
                    [
                        'version' => $data['version'],
                        'closedDate' => $now
                    ]
                )
            ]
        );
    }

    public function reopenEntity($id)
    {
        $data = $this->fetchData($id);
        $now = null;

        $this->getRestClient()->update(
            $data['id'],
            [
                'data' => json_encode(
                    [
                        'version' => $data['version'],
                        'closedDate' => $now
                    ]
                )
            ]
        );
    }
}
