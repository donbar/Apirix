<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2013-08-08
 * @license GPL-2.0
 * @url     <https://github.com/smalot/pdfparser>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Smalot\PdfParser;

use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Element\ElementBoolean;
use Smalot\PdfParser\Element\ElementDate;
use Smalot\PdfParser\Element\ElementHexa;
use Smalot\PdfParser\Element\ElementName;
use Smalot\PdfParser\Element\ElementNull;
use Smalot\PdfParser\Element\ElementNumeric;
use Smalot\PdfParser\Element\ElementString;
use Smalot\PdfParser\Element\ElementStruct;
use Smalot\PdfParser\Element\ElementXRef;

/**
 * Class Element
 *
 * @package Smalot\PdfParser
 */
class Element
{
    /**
     * @var Document
     */
    protected $document = null;

    /**
     * @var mixed
     */
    protected $value = null;

    /**
     * @param mixed    $value
     * @param Document $document
     */
    public function __construct($value, Document $document = null)
    {
        $this->value    = $value;
        $this->document = $document;
    }

    /**
     *
     */
    public function init()
    {

    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function equals($value)
    {
        return ($value == $this->value);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function contains($value)
    {
        if (is_array($this->value)) {
            /** @var Element $val */
            foreach ($this->value as $val) {
                if ($val->equals($value)) {
                    return true;
                }
            }

            return false;
        } else {
            return $this->equals($value);
        }
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)($this->value);
    }

    /**
     * @param string   $content
     * @param Document $document
     * @param int      $position
     *
     * @return array
     * @throws \Exception
     */
    public static function parse($content, Document $document = null, &$position = 0)
    {
        $args        = func_get_args();
        $only_values = isset($args[3]) ? $args[3] : false;
        $content     = trim($content);
        $values      = array();

        do {
            $old_position = $position;

            if (!$only_values) {
                if (!preg_match('/^\s*(?<name>\/[A-Z0-9\._]+)(?<value>.*)/si', substr($content, $position), $match)) {
                    break;
                } else {
                    $name     = ltrim($match['name'], '/');
                    $value    = $match['value'];
                    $position = strpos($content, $value, $position + strlen($match['name']));
                }
            } else {
                $name  = count($values);
                $value = substr($content, $position);
            }

            if ($element = ElementName::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementXRef::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementNumeric::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementStruct::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementBoolean::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementNull::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementDate::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementString::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementHexa::parse($value, $document, $position)) {
                $values[$name] = $element;
            } elseif ($element = ElementArray::parse($value, $document, $position)) {
                $values[$name] = $element;
            } else {
                $position = $old_position;
                break;
            }
        } while ($position < strlen($content));

        return $values;
    }
}
