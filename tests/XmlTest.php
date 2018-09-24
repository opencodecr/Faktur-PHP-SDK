<?php

    use Faktur\Xml;
    use PHPUnit\Framework\TestCase;

    final class XmlTest extends TestCase 
    {

        protected function setUp()
        {
            $_FILES = [
                'file' => [
                    'name' => 'factura001.xml',
                    'type' => 'application/xml',
                    'tmp_name' => realpath('tests/files/send/factura001.xml'),
                    'error' => 0,
                    'size' => 2760,
                ]
            ];

            \Faktur\Xml::uploadXmlFile('file', realpath('tests/files/receive'));
        }

        protected function tearDown()
        {
            unset($_FILES);
            @unlink(realpath('tests/files/receive/factura001.xml'));
        }
        
        public function testuploadXmlFile()
        {
            $success = \Faktur\Xml::receiveXml('file');
            $this->assertTrue($success);
        }
    
        
    }