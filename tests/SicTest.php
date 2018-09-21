<?php

    use opencode506\Faktur\Common;
    use PHPUnit\Framework\TestCase;

    final class SicTest extends TestCase 
    {

        public function testfindByDocumentoIdJuridico()
        {
            $common = new Common('DEV');
            $taxPayer = $common->findByDocumentId('310175361932', 'Juridico');
            $success = $taxPayer['nombre'];
            $this->assertNotEmpty($success);
        }
        
        public function testfindByDocumentoIdFisico()
        {
            $common = new Common('DEV');
            $taxPayer = $common->findByDocumentId('040167066109', 'Fisico');
            $success = $taxPayer['apellido1'];
            $this->assertNotEmpty($success);
        }

    }