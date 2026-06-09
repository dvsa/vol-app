<?php

namespace Olcs\XmlTools\Xml;

/**
 * Utility class for creating XML from an array of data
 *
 * Class XmlNodeBuilder
 * @package Olcs\XmlTools\Xml
 */
class XmlNodeBuilder extends \DOMDocument
{
    /**
     * @var String
     */
    private $rootElement;

    /**
     * @var Array
     */
    private $data;

    private $xmlNs;

    /**
     * @return String
     */
    public function getRootElement()
    {
        return $this->rootElement;
    }

    /**
     * @param String $rootElement
     */
    public function setRootElement($rootElement): void
    {
        $this->rootElement = $rootElement;
    }

    /**
     * @return Array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param Array $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return String
     */
    public function getXmlNs()
    {
        return $this->xmlNs;
    }

    /**
     * @param String $xmlNs
     */
    public function setXmlNs($xmlNs): void
    {
        $this->xmlNs = $xmlNs;
    }

    /**
     * @param $rootElement
     * @param $xmlNs
     */
    public function __construct($rootElement, $xmlNs, array $data)
    {
        parent:: __construct('1.0', 'UTF-8');

        $this->setRootElement($rootElement);
        $this->setXmlNs($xmlNs);
        $this->setData($data);
        $this->formatOutput = true;
    }

    /**
     * @return mixed
     */
    public function buildTemplate()
    {
        $domElement = $this->createElementNs($this->getXmlNs(), $this->getRootElement());
        $document = $this->createFromArray($this->getData(), $domElement);
        $this->appendChild($document);

        return $this->saveXML();
    }

    /**
     * Creates the XML document, requires an array of data and a parent element
     *
     * @return \DOMElement|null
     */
    private function createFromArray(array $data, \DOMElement $domElement = null): ?\DOMElement
    {
        foreach ($data as $values) {
            //create the element, and give it a value is it has one
            if (isset($values['value'])) {
                $newElement = $this->createElement($values['name'], $values['value']);
            } else {
                $newElement = $this->createElement($values['name']);
            }

            //if the element has attributes, create them first
            if (isset($values['attributes'])) {
                foreach ($values['attributes'] as $attribute => $attributeValue) {
                    $newElement->setAttribute($attribute, $attributeValue);
                }
            }

            //if the element has child nodes
            if (isset($values['nodes'])) {
                self::createFromArray($values['nodes'], $newElement);
            }

            $domElement->appendChild($newElement);
        }

        return $domElement;
    }
}
