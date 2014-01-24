<?php
/**
 * View Helper for formatting datetimes
 *
 * Helps formatting dates in views.
 * 
 * @package     OlcsSelfserve
 * @subpackage  view
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace OlcsSelfserve\View\Helper;

use DateTime;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Formats a datetime
 */
class SimpleDateFormat extends AbstractHelper implements ServiceLocatorAwareInterface
{
    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

    /**
     * Formats a datetime
     *
     * @param string|int|DateTime $value  The date/datetime to format
     * @param string              $format The name of the format or the format itself in strftime() format
     * @param string              $empty  What to print instead of an empty string when the timestamp is empty
     * @return string The formatted datetime
     */
    public function __invoke($value, $format = 'default', $empty = '')
    {
        if ($value === '' || $value === null) {
            return $empty;
        } elseif (is_int($value)) {
            $value = new DateTime('@' . $value);
        } elseif (!$value instanceof DateTime) {
            $value = new DateTime($value);
        }

        if (!$value->getTimestamp()) {
            return $empty;
        }

        $config = $this->getServiceLocator()->getServiceLocator()->get('Config');
        $formatConfig = isset($config['simple_date_format']) ? $config['simple_date_format'] : array();

        $format = isset($formatConfig[$format]) ? $formatConfig[$format] : $format;

        if ($format == 'default') {
            $format = DateTime::ISO8601;
        }

        return $value->format($format);
    }
}
