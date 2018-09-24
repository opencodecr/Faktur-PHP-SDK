<?php

namespace Faktur;

/**
 * Xml representa los eventos que se neesitan para la 
 * construcción, conversión, firmado y enviado de los
 * diferentes XLS
 * 
 * @author Open Code 506 community <opencode506@gmail.com>
 * @since 1.0.0
 */
class Xml  {

    public static $return = [];

    /**
     * encodeXmlTob64
     * Codifica un XML a MIME base 64
     *
     * @param string $xml
     * @return string
     */
    public static function encodeXmlTob64($xml)
    {
        return \base64_encode($xml);
    }

    /**
     * decode64toXml
     * Deodifica un XML en MIME base 64 a un string
     * 
     * @param [type] $xml
     * @return string
     */
    public static function decode64toXml($xml)
    {
        return \base64_decode($xml);
    }

    /**
     * uploadXmlFile
     *
     * @param string $xmlFile Nombre del input que envío el archivo
     * @param string $xmlPathNameDestination Destino en donde se almacenará el XML
     * @return boolean
     */
    public static function uploadXmlFile($xmlFile, $postFile, $destination)
    {

        try {
            // Emitimos un error si no se envío ningún archivo
            if (empty($postFile[$xmlFile])) 
                throw new \Exception("No se envíó ningún archivo", 500);
            
            // Verificamos que lo que se esté enviado sea un XML
            if ($postFile[$xmlFile]['type'] != 'application/xml') 
                throw new \Exception("El archivo no es XML", 500);
            
            // Verificamos si el archivo fue cargado
            if (!file_exists($postFile[$xmlFile]['tmp_name'])) 
                throw new \Exception("El archivo no fue cargado", 500);

            // Chequeamos que no exista un archivo con el mismo nombre en la misma ruta
            $fileName = $destination . '/' . $postFile[$xmlFile]['name'];

            // Verificamos si ya existe un archivo que se llame igual en el destino
            if (file_exists($fileName)) {
                // Como el archivo existe le generamos un sufijo al nombre, 
                // para esto le agregamos un timestamp y le delegamos al usuaio
                // hacer la corrección manualmente
                $date = new \DateTime();
                $timestamp = $date->getTimestamp();

                // Le añadimos un diferenciador al nombre del archivo
                $fileName = $destination . '/' . $postFile[$xmlFile]['name'] . '_copy_' . $timestamp;
            }


            if (move_uploaded_file($postFile[$xmlFile]['tmp_name'], $fileName)) {
                self::$return = [
                    'code' => 200,
                    'message' => "El archivo XML cargó con éxito"
                ];
            } elseif (copy($postFile[$xmlFile]['tmp_name'], $fileName)) {
                // Tratamos de copiarlo
                self::$return = [
                    'code' => 200,
                    'message' => "El archivo XML cargó con éxito"
                ];
            } 
                        
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function receiveXml($name, $postFile) 
    {
        if (empty($postFile[$name]) || !\file_exists($postFile[$name]['tmp_name'])) 
            return false;
        
        return true;
    }

}
