<?php

namespace App\Services;

/**
 * Class XmlService
 * @package App\Services
 */
class XmlService
{

    /**
     * @var
     */
    private static $xml;

    public static function loadOpacXML(string $xmlRaw): array
    {
        libxml_use_internal_errors(true);
        self::$xml = new \DOMDocument('1.0', 'utf-8');
        self::$xml->loadXML($xmlRaw);
        return self::processArray(self::$xml);
    }

    public static function processArray($xml)
    {
        $result = array();
        if ($xml->hasAttributes()) {
            $attrs = $xml->attributes;

            foreach ($attrs as $attr) {
                if ($attr->name != 'name') {
                    $result[$attr->name] = $attr->value;
                }
            }
        }

        if ($xml->hasChildNodes()) {
            $children = $xml->childNodes;
            if ($children->length == 1) {

                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE) {
                    $result['_value'] = $child->nodeValue;
//                    dump( $child->nodeValue);
                    if (count($result) == 1) {
                        return $result['_value'];
                    } else {
                        return $result;
                    }
                } elseif ($child->nodeType == XML_CDATA_SECTION_NODE) {
                    return $child->nodeValue;
                }
            }
            $groups = array();
            foreach ($children as $child) {
                $hasAttributes = $child->hasAttributes();
                if ($hasAttributes) {
                    $keyValue = null;
                    foreach ($child->attributes as $k => $v) {
                        if ($k == 'name') {
                            $keyValue = $v->value;
                        }
                    }
                }
                $keyName = $keyValue ?? $child->nodeName;

                if (!isset($result[$keyName])) {
                    $childNode = self::processArray($child);
                    if (is_string($childNode)) {
                        $result[$keyName] = $childNode;
                    } elseif (is_array($childNode)) {
                        if (count($childNode) > 0) {
                            $result[$keyName] = $childNode;
                        } else {
                            $result[$keyName] = '';
                        }
                    }

                } else {
                    if (!isset($groups[$child->nodeName]) && $child->nodeType != XML_TEXT_NODE) {
                        $result[$child->nodeName] = array($result[$child->nodeName]);
                        $groups[$child->nodeName] = 1;
                    }
                    $childNode = self::processArray($child);

                    if (is_string($childNode) || (is_array($childNode) && count($childNode) > 0)) {
                        $result[$child->nodeName][] = $childNode;
                    }
                }
            }
        }
        return $result;
    }

}
