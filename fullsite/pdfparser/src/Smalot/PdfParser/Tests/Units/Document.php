<?php

/**
 * @file
 * This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2013-08-08
 * @license GPL-2.0
 * @url     <https://github.com/smalot/pdfparser>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Smalot\PdfParser\Tests\Units;

use mageekguy\atoum;

/**
 * Class Document
 * @package Smalot\PdfParser\Tests\Units
 */
class Document extends atoum\test
{
    public function testSetObjects()
    {
        $document = new \Smalot\PdfParser\Document();
        $object   = new \Smalot\PdfParser\Object($document);
        // Obj #1 is missing
        $this->assert->variable($document->getObjectById(1))->isNull();
        $document->setObjects(array(1 => $object));
        // Obj #1 exists
        $this->assert->object($document->getObjectById(1))->isInstanceOf('\Smalot\PdfParser\Object');

        $content  = '<</Type/Page>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object   = new \Smalot\PdfParser\Object($document, $header);
        $document->setObjects(array(2 => $object));
        // Obj #1 is missing
        $this->assert->assert->variable($document->getObjectById(1))->isNull();
        // Obj #2 exists
        $this->assert->object($document->getObjectById(2))->isInstanceOf('\Smalot\PdfParser\Object');
    }

    public function testGetObjects()
    {
        $document = new \Smalot\PdfParser\Document();
        $object1  = new \Smalot\PdfParser\Object($document);
        $content  = '<</Type/Page>>unparsed content';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);

        $object2  = new \Smalot\PdfParser\Page($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2));

        $this->assert->integer(count($objects = $document->getObjects()))->isEqualTo(2);
        $this->assert->object($objects[1])->isInstanceOf('\Smalot\PdfParser\Object');
        $this->assert->object($objects[2])->isInstanceOf('\Smalot\PdfParser\Object');
        $this->assert->object($objects[2])->isInstanceOf('\Smalot\PdfParser\Page');
    }

    public function testDictionary()
    {
        $document = new \Smalot\PdfParser\Document();
        $this->assert->integer(count($objects = $document->getDictionary()))->isEqualTo(0);
        $object1  = new \Smalot\PdfParser\Object($document);
        $content  = '<</Type/Page>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object2  = new \Smalot\PdfParser\Page($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2));
        $this->assert->integer(count($objects = $document->getDictionary()))->isEqualTo(1);
        $this->assert->integer(count($objects['Page']))->isEqualTo(1);
        $this->assert->integer($objects['Page'][2])->isEqualTo(2);
    }

    public function testGetObjectsByType()
    {
        $document = new \Smalot\PdfParser\Document();
        $object1  = new \Smalot\PdfParser\Object($document);
        $content  = '<</Type/Page>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object2  = new \Smalot\PdfParser\Page($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2));
        $this->assert->integer(count($objects = $document->getObjectsByType('Page')))->isEqualTo(1);
        $this->assert->object($objects[2])->isInstanceOf('\Smalot\PdfParser\Object');
        $this->assert->object($objects[2])->isInstanceOf('\Smalot\PdfParser\Page');
    }

    public function testGetPages()
    {
        // Missing catalog
        $document = new \Smalot\PdfParser\Document();
        try {
            $pages = $document->getPages();
            $this->assert->boolean($pages)->isEqualTo(false);
        } catch(\Exception $e) {
            $this->assert->object($e)->isInstanceOf('\Exception');
        }

        // Listing pages from type Page
        $content  = '<</Type/Page>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object1  = new \Smalot\PdfParser\Page($document, $header);
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object2  = new \Smalot\PdfParser\Page($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2));
        $pages = $document->getPages();
        $this->assert->integer(count($pages))->isEqualTo(2);
        $this->assert->object($pages[0])->isInstanceOf('\Smalot\PdfParser\Page');
        $this->assert->object($pages[1])->isInstanceOf('\Smalot\PdfParser\Page');

        // Listing pages from type Pages (kids)
        $content  = '<</Type/Page>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object1  = new \Smalot\PdfParser\Page($document, $header);
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object2  = new \Smalot\PdfParser\Page($document, $header);
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object3  = new \Smalot\PdfParser\Page($document, $header);
        $content  = '<</Type/Pages/Kids[1 0 R 2 0 R]>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object4  = new \Smalot\PdfParser\Pages($document, $header);
        $content  = '<</Type/Pages/Kids[3 0 R]>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object5  = new \Smalot\PdfParser\Pages($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2, 3 => $object3, 4 => $object4, 5 => $object5));
        $pages = $document->getPages();
        $this->assert->integer(count($pages))->isEqualTo(3);
        $this->assert->object($pages[0])->isInstanceOf('\Smalot\PdfParser\Page');
        $this->assert->object($pages[1])->isInstanceOf('\Smalot\PdfParser\Page');
        $this->assert->object($pages[2])->isInstanceOf('\Smalot\PdfParser\Page');

        // Listing pages from type Catalog
        $content  = '<</Type/Page>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object1  = new \Smalot\PdfParser\Page($document, $header);
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object2  = new \Smalot\PdfParser\Page($document, $header);
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object3  = new \Smalot\PdfParser\Page($document, $header);
        $content  = '<</Type/Pages/Kids[1 0 R 2 0 R]>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object4  = new \Smalot\PdfParser\Pages($document, $header);
        $content  = '<</Type/Pages/Kids[4 0 R 3 0 R]>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object5  = new \Smalot\PdfParser\Pages($document, $header);
        $content  = '<</Type/Catalog/Pages 5 0 R >>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object6  = new \Smalot\PdfParser\Pages($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2, 3 => $object3, 4 => $object4, 5 => $object5, 6 => $object6));
        $pages = $document->getPages();
        $this->assert->integer(count($pages))->isEqualTo(3);
        $this->assert->object($pages[0])->isInstanceOf('\Smalot\PdfParser\Page');
        $this->assert->object($pages[1])->isInstanceOf('\Smalot\PdfParser\Page');
        $this->assert->object($pages[2])->isInstanceOf('\Smalot\PdfParser\Page');
    }

    public function testParseFile()
    {
        $filename = 'samples/Document1_foxitreader.pdf';
        $document = \Smalot\PdfParser\Document::parseFile($filename);
        $this->assert->object($document)->isInstanceOf('\Smalot\PdfParser\Document');

        try {
            // Test unable de read file.
            $filename = 'missing.pdf';
            $document = \Smalot\PdfParser\Document::parseFile($filename);
            $this->assert->object($document)->isInstanceOf('null');
        } catch (\mageekguy\atoum\exceptions\logic $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->assert->exception($e)->hasMessage('Unable to read file.');
        }

        try {
            // Test missing startxref position.
            $filename = tempnam(sys_get_temp_dir(), 'test_');
            $document = \Smalot\PdfParser\Document::parseFile($filename);
            unlink($filename);
            $this->assert->object($document)->isInstanceOf('null');
        } catch (\mageekguy\atoum\exceptions\logic $e) {
            throw $e;
        } catch (\Exception $e) {
            unlink($filename);
            $this->assert->exception($e)->hasMessage('Missing "startxref" tag.');
        }

        try {
            // Test invalid structure.
            $filename = tempnam(sys_get_temp_dir(), 'test_');
            $content = <<<EOF
%PDF-1.4
%Çì¢
1 0 obj
<</Length 6 0 R/Filter /FlateDecode>>
stream
foo
endstream
endobj
2 0 obj
<</Length 6 0 R/Filter /FlateDecode>>
stream
foo
endstream
3 0 obj
<</Length 6 0 R/Filter /FlateDecode>>
stream
foo
endstream
endobj
invalid section
xref
0 3
0000000000 65535 f
0000000019 00000 n
0000000093 00000 n
0000000160 00000 n
0000000234 00000 n
trailer
<< /Size 24 /Root 1 0 R /Info 2 0 R
/ID [<984DA96B7C5E60408BB1AFE2FE6B6C03><984DA96B7C5E60408BB1AFE2FE6B6C03>]
>>
startxref
250
%%EOF

EOF;
            file_put_contents($filename, $content);
            $document = \Smalot\PdfParser\Document::parseFile($filename);
            $this->assert->object($document)->isInstanceOf('null');
            unlink($filename);
        } catch (\mageekguy\atoum\exceptions\logic $e) {
            @unlink($filename);
            throw $e;
        } catch (\Exception $e) {
            @unlink($filename);
            $this->assert->exception($e)->hasMessage('Invalid object declaration.');
        }
    }

    public function testParseContent()
    {
        $content = <<<EOT
5 0 obj
5198
endobj
2 0 obj
<< /Type /Page /Parent 3 0 R /Resources 6 0 R /Contents 4 0 R /MediaBox [0 0 595.32 841.92]
>>
endobj
6 0 obj
<< /ProcSet [ /PDF /Text /ImageB /ImageC /ImageI ] /ColorSpace << /Cs1 13 0 R
/Cs2 14 0 R >> /Font << /F5.1 16 0 R /F1.0 7 0 R /F4.1 12 0 R /F2.1 9 0 R
/F3.0 10 0 R >> /XObject << /Im1 17 0 R >> >>
endobj
EOT;
        $document = \Smalot\PdfParser\Document::parseContent($content);
        $this->assert->object($document)->isInstanceOf('\Smalot\PdfParser\Document');
        $this->assert->array($document->getObjects())->hasSize(3);
        $this->assert->string($document->getObjectById(5)->getContent())->isEqualTo('5198');
        $this->assert->castToString($document->getObjectById(2)->get('Type'))->isEqualTo('Page');
        $object6 = $document->getObjectById(6)->get('ProcSet');
        $this->assert->object($object6)->isInstanceOf('\Smalot\PdfParser\Element\ElementArray');
    }
}
