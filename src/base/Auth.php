<?php

namespace Faktur;

use Faktur\Interfaces\AuthInterface;

/**
 * Auth representa los eventos comunes que se necesitan para
 * generar los comprobantes electrónicos
 * 
 * @author Open Code 506 community <opencode506@gmail.com>
 * @since 1.0.0
 */
class Auth implements AuthInterface {

    /**
     * @var Indica en que ambiente se está ejecutando las acciones
     */
    private $environment;

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
                'headers' => \Faktur\Helpers\AuthHelper::getHeadersFromCurlResponse($response),
                'body' => (array) json_decode(substr($response, $status['header_size']))
            ];
            
        } catch (\Exception $e) {
            return $e;
        }
    }

}
