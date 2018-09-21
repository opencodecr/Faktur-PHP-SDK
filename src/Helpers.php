<?php

namespace opencode506\Faktur;

class Helpers {

    /**
     * Obtener los headers de los responses envíados por Hacienda
     *
     * @param [string] $response
     * @return void
     */
    public function get_headers_from_curl_response($response)
    {
        $headers = [];
        $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

        foreach (explode("\r\n", $header_text) as $i => $line)
            if ($i === 0) return $headers['http_code'] = $line;
            
            list ($key, $value) = explode(': ', $line);
            $headers[$key] = $value;
            
        return $headers;
    }

    public function ping($domain)
    {
        $starttime = microtime(true);
        $file      = fsockopen ($domain, 80, $errno, $errstr, 10);
        $stoptime  = microtime(true);
        $status    = 0;

        if (!$file) {
            // Sito offline
            $status = false;
        } else {
            fclose($file);
            $status = ($stoptime - $starttime) * 1000;
            $status = floor($status);
        }
        return $status;
    }

}