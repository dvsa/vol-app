<?php
/**
 * Controller plugin for creating Doctrine transactions
 *
 * @package     olcs
 * @subpackage  plugin
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class DoctrineTransaction extends AbstractPlugin
{
    /**
     * Executes a function in a transaction.
     *
     * @param  callable $func The function to execute transactionally.
     * @return mixed          The non-empty value returned from the closure or true instead.
     */
    public function __invoke($func)
    {
        return $this->getController()
                    ->getServiceLocator()
                    ->get('doctrine.entitymanager.orm_default')
                    ->transactional($func);
    }
}
