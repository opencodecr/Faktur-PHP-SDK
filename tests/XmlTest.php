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
            $postFiles = $_FILES;

            \Faktur\Xml::uploadXmlFile('file', $postFiles, realpath('tests/files/receive'));
        }

        protected function tearDown()
        {
            @unlink(realpath('tests/files/receive/factura001.xml'));
        }
        
        public function testuploadXmlFile()
        {
            $postFiles = $_FILES;
            $success = \Faktur\Xml::receiveXml('file', $postFiles);
            $this->assertTrue($success);
        }
    
        
    }