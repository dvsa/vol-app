<?php
/**
 * An abstract controller that all OLCS restful controllers inherit from
 *
 * @package     olcscommon
 * @subpackage  controller
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace OlcsCommon\Controller;

abstract class AbstractRestfulController extends \Zend\Mvc\Controller\AbstractRestfulController
{
    use \OlcsCommon\Utility\ResolveApiTrait;

    const ERROR_METHOD_NOT_ALLOWED = 101;
    const ERROR_FORBIDDEN = 102;
    const ERROR_MISSING_PARAMETERS = 103;
    const ERROR_INVALID_PARAMETER = 104;
    const ERROR_UNKNOWN = 105;
    const ERROR_NOT_FOUND = 106;
    const ERROR_NOT_IMPLEMENTED = 107;
    const ERROR_CONFLICT = 108;

    /**
     * Return only specified valid keys from supplied array
     *
     * @param  array $values The array with the values to be filtered
     * @param  array $keys   The valid keys to allow
     * @return array
     */
    protected function pickValidKeys(array $values, array $keys)
    {
        return array_intersect_key($values, array_flip($keys));
    }

    /**
     * Returns the version specified in the request
     *
     * @return int
     */
    protected function getVersion()
    {
        return $this->request->getQuery()->get('version', false);
    }
}
