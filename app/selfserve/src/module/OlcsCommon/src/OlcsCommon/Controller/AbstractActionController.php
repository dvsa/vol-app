<?php
/**
 * An abstract controller that all ordinary OLCS controllers inherit from
 *
 * @package     olcscommon
 * @subpackage  controller
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace OlcsCommon\Controller;

abstract class AbstractActionController extends \Zend\Mvc\Controller\AbstractRestfulController
{
    use \OlcsCommon\Utility\ResolveApiTrait;
}
