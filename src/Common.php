<?php

namespace opencode506\Faktur;

use opencode506\Faktur\Helpers;
use RebaseData\Client;

/**
 * Clase Common
 * 
 */
class Common extends Helpers {

    CONST IDP_PRODUCTION = [
        'URL_TOKEN'  => 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token',
        'CLIENT_ID'  => 'api-prod'
    ];

    CONST IDP_SANDBOX = [
        'URL_TOKEN'  => 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token',
        'CLIENT_ID'  => 'api-stag'
    ];

    CONST SIC_IP = '196.40.56.201';
    CONST SIC_WEB_SERVICE = 'wsInformativasSICWEB/Service1.asmx?WSDL';

    var $environment;
    var $sicHostWS;

    public function __construct() 
    {
        // Establecemos algunos valores necesarios para la consulta SIC Web
        ini_set('soap.wsdl_cache_enabled', '0');
        ini_set('soap.wsdl_cache_ttl', 900);
        ini_set('default_socket_timeout', 30);

        $this->sicHostWS = 'http://' . self::SIC_IP . '/' . self::SIC_WEB_SERVICE;
    }

    /**
     * Token
     * 
     * Permite obtener un token a partir del usuario y contraseña 
     * generados desde ATV de Hacienda hasta refrescar el token ya una vez 
     * obtenido
     * 
     * 
     *
     * @param array $credential     Array indicando el ambiente(Producción o 
     *                              Sandbox), usuario y contraseña
     * @param string $grantType     String que indica el tipo de acción, 
     *                              'password' para obtener token, refresh_token
     *                              para refrescar el token
     * @return array                Retorna un array con los headers, body
     */
    public function token($credential, $grantType = 'password') 
    {
        
        try {

            // verificamos que el valor en grantType sea válido
            if ($grantType != 'password' && $grantType != 'refresh_token') throw new \Exception("Grant Type incorrecto", 500);
            
            // Establecemos los valores para obtener el token
            $this->environment = isset($credential['environment']) ? $credential['environment'] : 'DEV';

            $credentials = [
                'client_id'     => $this->environment == 'PROD' ? self::IDP_PRODUCTION['CLIENT_ID'] : self::IDP_SANDBOX['CLIENT_ID'], 
                'client_secret' => '',
                'grant_type'    => $grantType,
                'username'      => isset($credential['username']) ? $credential['username'] : '',
                'password'      => isset($credential['password']) ? $credential['password'] : ''
            ];

            // Si lo que queremos es refrescar el token, eliminamos el usuario y contraseña e incluídos el refresh token
            if ($grantType == 'refresh_token') {
                unset($credentials['username'], $credentials['password']);
                $credentials['refresh_token'] = $credential['refresh_token'];
            } 

            // Enviamos el request
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->environment == 'PROD' ? self::IDP_PRODUCTION['URL_TOKEN'] : self::IDP_SANDBOX['URL_TOKEN'], 
                CURLOPT_RETURNTRANSFER => true, 
                CURLOPT_HEADER => true, 
                CURLOPT_POST => false,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5, 
                CURLOPT_SSL_VERIFYPEER => false, 
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
                CURLOPT_POSTFIELDS => http_build_query($credentials),
            ]);
            
            // Ejecutamos el request y obtenemos algunos valores y estados
            $response = curl_exec($curl);
            $status = curl_getinfo($curl);
            curl_close($curl);
            
            return [
                'headers' => $this->get_headers_from_curl_response($response),
                'body' => (array) json_decode(substr($response, $status['header_size']))
            ];
            
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * findByDocumentoId
     * 
     * Permite obtener la información de una contribuyente por medio
     * de su número de identificación
     *
     * @param [type] $documentId    Número de identificación
     * @param string $origin        Tipo de identificación, puede ser Fisico, Juridico o DIMEX
     * @return void
     */    
    public function findByDocumentId($documentId, $origin = 'Juridico')
    {
        try {  

            $return = [];
            $options = [
                'uri'                => 'http://schemas.xmlsoap.org/soap/envelope/',
                'style'              => SOAP_RPC,
                'use'                => SOAP_ENCODED,
                'soap_version'       => SOAP_1_1,
                'cache_wsdl'         => WSDL_CACHE_NONE,
                'connection_timeout' => 30,
                'trace'              => true,
                'encoding'           => 'UTF-8',
                'exceptions'         => true,
                'compression'        => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE
            ];
        
            // El webservice en Hacienda hace la consulta utilizando los valores
            // de parámetro que no estén vacíos, así que se puede hacer una consulta
            // haciendo combinaciones.
            $params = [
                'origen'      => $origin, // Fisico,  Juridico o DIMEX
                'cedula'      => $documentId
            ];
        
            $wsdl = "http://196.40.56.20/wsInformativasSICWEB/Service1.asmx?WSDL";
        
            $soap = new \SoapClient($wsdl, $options);
            $response = $soap->ObtenerDatos($params);
            $soap_response = $response->ObtenerDatosResult->any;

            $xml = str_replace(["diffgr:", "msdata:"], '', $soap_response);
            $xml = "<package>" . $xml . "</package>";
            $data = simplexml_load_string($xml);

            if ($origin == 'Fisico') {
                $return = [
                    'cedula' => isset($data->diffgram->DocumentElement->Table->CEDULA[0]) ? $data->diffgram->DocumentElement->Table->CEDULA[0] : '',
                    'apellido1' => isset($data->diffgram->DocumentElement->Table->APELLIDO1[0]) ? $data->diffgram->DocumentElement->Table->APELLIDO1[0] : '',
                    'apellido2' => isset($data->diffgram->DocumentElement->Table->APELLIDO2[0]) ? $data->diffgram->DocumentElement->Table->APELLIDO2[0] : '',
                    'nombre1' => isset($data->diffgram->DocumentElement->Table->NOMBRE1[0]) ? $data->diffgram->DocumentElement->Table->NOMBRE1[0] : '',
                    'nombre2' => isset($data->diffgram->DocumentElement->Table->NOMBRE2[0]) ? $data->diffgram->DocumentElement->Table->NOMBRE2[0] : '',
                    'adm' => isset($data->diffgram->DocumentElement->Table->ADM[0]) ? $data->diffgram->DocumentElement->Table->ADM[0] : '',
                    'ori'=> isset($data->diffgram->DocumentElement->Table->ORI[0]) ? $data->diffgram->DocumentElement->Table->ORI[0] : ''
                ];
            } elseif ($origin == 'Juridico') {
                $return = [
                    'cedula' => isset($data->diffgram->DocumentElement->Table->CEDULA[0]) ? $data->diffgram->DocumentElement->Table->CEDULA[0] : '',
                    'nombre' => isset($data->diffgram->DocumentElement->Table->NOMBRE[0]) ? $data->diffgram->DocumentElement->Table->NOMBRE[0] : '',
                    'adm' => isset($data->diffgram->DocumentElement->Table->ADM[0]) ? $data->diffgram->DocumentElement->Table->ADM[0] : '',
                    'ori'=> isset($data->diffgram->DocumentElement->Table->ORI[0]) ? $data->diffgram->DocumentElement->Table->ORI[0] : ''
                ];
            } 
             
            return $return;

        } catch (\SoapFault $fault) {
            return [
                'code' => 500,
                'message' => $fault->getMessage()
            ];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
