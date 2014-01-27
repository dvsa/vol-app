<?php
/**
 * Generic form used to track added and removed ID:s in a list of entities
 *
 * @package    olcs
 * @subpackage application
 * @author     Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace OlcsSelfserve\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class OlcsSelfserveForm extends Form implements ServiceLocatorAwareInterface
{
    
    protected $servicelocator;
    
    /**
     *  Available categories. Second level has keys that are the category id:s.
     *  @var array Available categories
     */
    protected static $categories = array(
        'Compliance' => array(
            1 => 'Offences (inc. driver hours)',
            2 => 'Prohibitions',
            3 => 'Convictions',
            4 => 'Penalties',
            5 => 'ERRU MSI',
            6 => 'Bus compliance',
            7 => 'Section 9',
            8 => 'Section 43',
            9 => 'Impounding'
        ),
        'Bus registration' => array(),
        'TM' => array(
            10 => 'Duplicate TM',
            11 => 'Repute / professional comptenece of TM',
            12 => 'TM Hours'
        ),
        'Licensing application' => array(
            13 => 'Interim with / without submission',
            14 => 'Representation',
            15 => 'Objection',
            16 => 'Non-chargeable variation',
            17 => 'Regulation 31',
            18 => 'Schedule 4',	
            19 => 'Chargeable variation',
            20 => 'New application'
        ),
        'Licence referral' => array(
            21 => 'Surrender',
            22 => 'Non application related maintenance issue',
            23 => 'Review complaint',
            24 => 'Late fee',
            25 => 'Financial standing issue (continuation)',
            26 => 'Repute fitness of director',
            27 => 'Period of grace',
            28 => 'Proposal to revoke'
        )
    );
    
    //Case details page
    protected static $caseDetailTypes = array(
        1 => 'Generate submission',
        2 => 'Warning letter',
        3 => 'NFA',
        4 => 'Section 9',
        5 => 'Section 43',
        6 => 'In-office revocation'
    );
    
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
    }
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->servicelocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->servicelocator;
    }
    
    public function getResourceString($string)
    {
        return isset($this->getResourceStrings()[$string]) ? $this->getResourceStrings()[$string] : null;
    }
    
    /*
     * Gets options for a select with an optional label
     */
    public function setSelect($options, $label=null)
    {
        if (!empty($label)) $returnOptions = array('' => $label);
        foreach ($options as $key => $option) {
            $returnOptions[$key] = $option;
        }
        return $returnOptions;
    }
    
    protected function getSelectResourceStrings($options)
    {
        $resources = $this->getResourceStrings();
        $resourceHelper = new \Olcs\View\Helper\ResourceHelper($resources);
        foreach($options as $key => $value) {
            $value = str_replace(' ', '-',   strtolower($value));
            $retOptions[$key] = $resourceHelper($value);
        }
        return $retOptions;
    }

    protected function getResourceStrings() {

        $reader = new \Zend\Config\Reader\Ini();
        $data   = $reader->fromFile(__DIR__ . '/../../../config/application.ini');
        return $data['section'];

    }
    
}
