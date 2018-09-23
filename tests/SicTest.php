<?php

    use opencode506\Faktur\Common;
    use PHPUnit\Framework\TestCase;

    final class SicTest extends TestCase 
    {

        public function testfindByDocumentoIdJuridico()
        {
            $common = new Common('DEV');
            $taxPayer = $common->findByDocumentId('310175361932');
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

        public function testfindByNameFisico()
        {
            $common = new Common('DEV');
            $taxPayer = $common->findByName(['NOMBRE2' => 'Alejandro', 'APELLIDO1' => 'Benavides'], 'Fisico');
            $success = $taxPayer[0]['cedula'];
            $this->assertNotEmpty($success);
        }

        public function testfindByNameJuridico()
        {
            $common = new Common('DEV');
            $taxPayer = $common->findByName(['RAZON' => 'Meddyg'], 'Juridico');
            $success = $taxPayer[0]['cedula'];
            $this->assertNotEmpty($success);
        }

    }