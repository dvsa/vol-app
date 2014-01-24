<?php

namespace OlcsSelfserve\View\Helper;
use Zend\View\Helper\AbstractHelper;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ResourceHelper
 *
 * @author valtechuk
 */
class ResourceHelper extends AbstractHelper
{
    protected $resourceStrings;

    public function __construct(Array $strings)
    {
        $this->resourceStrings = $strings;
         }

    public function __invoke($key, Array $placeHolders = array())
    {
        if (isset($this->resourceStrings[$key])) {
            $value = str_replace(array_keys($placeHolders),array_values($placeHolders), $this->resourceStrings[$key]);
        } else {
            $value = '';
        }
        return $value;
    }
}
