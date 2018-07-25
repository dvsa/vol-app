<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Permits\Form\Validator;

use Zend\Validator\Between;

class CustomBetween extends Between
{
    const  TOO_LARGE = "tooLarge";
    const  TOO_SMALL = "tooSmall";

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::TOO_SMALL      => "You must enter a number which is '%min%' or more.",
        self::TOO_LARGE      => "You must enter a number equal to or less than your total vehicle authority. You must correct it."
    );


    public function isValid($value)
    {
        $this->setValue($value);

        if($this->getMin() > $value) {
            $this->error( self::TOO_SMALL );
            return false;
        }

        if($value > $this->getMax()) {
            $this->error( self::TOO_LARGE);
            return false;
        }

        return true;
    }
}
