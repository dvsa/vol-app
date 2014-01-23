<?php
/**
 * OLCS Common Configuration
 *
 * @package     olcscommon
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

/**
 * Module configuration
 */
return array(
    'service_manager' => array(
        'factories' => array(
            'ServiceApiResolver' => 'OlcsCommon\Service\ServiceApiResolver',
        ),
    ),
);
