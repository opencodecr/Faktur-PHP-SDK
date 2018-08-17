<?php

namespace opencode506\Faktur;

use opencode506\Faktur\Helpers;


class Common extends Helpers {

    CONST IDP_PRODUCTION = [
        'URL_TOKEN'  => 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token',
        'CLIENT_ID'  => 'api-prod'
    ];

    CONST IDP_SANDBOX = [
        'URL_TOKEN'  => 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token',
        'CLIENT_ID'  => 'api-stag'
    ];


    /**
     * Método Publico que nos permite obtener el token nuevo
     */

     /**
      * Obtiene y/o Refresca el token 
      *
      * @param string $grantType        Password para solicitar tokem, refresh_token para refrescar el token
      * @param array $credential        Admite un array para solicitar el token [usuario, password], para refrescar [grant_type => 'refresh_token']
      * @param boolean $isProduction    Por defecto es false, así que los request se envían a el ambiente Sandbox, 
      *                                 cambiar a true para producción
      * @return void
      */
    public static function token($grantType = 'password', $credential, $isProduction = false) 
    {
        // TODO: Aún se puede optimizar la captura de los parametros, enviando en el array
        // de credentials el usuario y contrasela para solicitar el token y enviado solo el 
        // token para refrescarlo, para poder identificar que operación se esta solicitando 
        // el método verifica cuantas variables se obtienen del array $cdredential
        
        try {

            // Verificamos que todos los parametros se hayan seteado
            if (!$grantType || !$credential) throw new \Exception("Uno o varios parametros no fueron definidos", 500);

            // verificamos que el valor en grantType sea válido
            if ($grantType != 'password' && $grantType != 'refresh_token') throw new \Exception("Grant Type incorrecto", 500);
            
            // Establecemos los valores para obtener el token
            $credentials = [
                'client_id'     => $isProduction ? self::IDP_PRODUCTION['CLIENT_ID'] : self::IDP_SANDBOX['CLIENT_ID'], 
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
                CURLOPT_URL => $isProduction ? self::IDP_PRODUCTION['URL_TOKEN'] : self::IDP_SANDBOX['URL_TOKEN'], 
                CURLOPT_RETURNTRANSFER => true, 
                CURLOPT_HEADER => true, 
                CURLOPT_POST => true, 
                CURLOPT_SSL_VERIFYPEER => false, 
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
                CURLOPT_POSTFIELDS => http_build_query($credentials),
            ]);
            
            // Ejecutamos el request y obtenemos algunos valores y estados
            $response = curl_exec($curl);
            $status = curl_getinfo($curl);
            $error = json_decode(curl_error($curl));
            curl_close($curl);
            
            // Envíamos lo que obtenemos del request
            if ($error) {
                return [
                    'status' => $status,
                    'message' => $error
                ];
            } else {
                return [
                    'headers' => self::get_headers_from_curl_response($response),
                    'body' => (array) json_decode(substr($response, $status['header_size'])),
                ];
            }
            
        } catch (\Exception $e) {
            echo 'Excepción capturada: ',  $e->getMessage(), "\n";
        }
    }


}