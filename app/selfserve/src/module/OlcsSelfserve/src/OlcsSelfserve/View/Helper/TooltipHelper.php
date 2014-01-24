<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GenericListHelper
 *
 * @author adminmwc
 */

namespace Olcs\View\Helper;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class TooltipHelper  extends AbstractHelper {
    
    public function __invoke(array $tooltipText, $type) {
        
        
        $resp = call_user_func(array($this, 'get'.$type), $tooltipText);
        return (string) $resp;
    
    }
    
    private function getAddInfo($tipText) {
        
        $tooltip = '<div class=text-left>';
        foreach($tipText as $label => $text) {
            $tooltip .= "<span class=ttip-label>$label:</span>  <span>$text</span><br>";
        }
        $tooltip .= '<div>';
        return $tooltip;
    }
    
}

?>
