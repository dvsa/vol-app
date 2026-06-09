<?php

namespace Olcs\XmlTools\Filter;

use Laminas\Filter\AbstractFilter;
use Laminas\Filter\Exception;
use DOMDocument;
use Laminas\Xml\Security;

/**
 * Class ParseXmlString
 * @package Olcs\XmlTools\Filter
 * @psalm-suppress TooManyTemplateParams
 * @template-extends AbstractFilter<array>
 */
class ParseXmlString extends AbstractFilter
{
    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws Exception\RuntimeException If filtering $value is impossible
     * @return mixed
     */
    #[\Override]
    public function filter($value)
    {
        $domDocument = new DOMDocument();
        Security::scan($value, $domDocument);

        return $domDocument;
    }
}
