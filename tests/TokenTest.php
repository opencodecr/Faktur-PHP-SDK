<?php

    use opencode506\Faktur\Common;
    use PHPUnit\Framework\TestCase;

    final class TokenTest extends TestCase 
    {

        private $refreshToken;

        public function testGetToken()
        {
            $common = new Common();
            $token = $common->token('password', [
                'username' => 'cpj-3-101-753619@stag.comprobanteselectronicos.go.cr',
                'password' => ')^Qa!Qq5/]M;_iVW)p+)'
            ]);

            $success = isset($token['body']['refresh_token']);
            $this->assertTrue($success);

        }
        
        public function testRefreshToken()
        {
            $common = new Common();
            $token = $common->token('password', [
                'username' => 'cpj-3-101-753619@stag.comprobanteselectronicos.go.cr',
                'password' => ')^Qa!Qq5/]M;_iVW)p+)'
            ]);

            $this->refreshToken = $token['body']['refresh_token'];
            
            $refreshToken = $common->token('refresh_token', [
                'refresh_token' => $this->refreshToken
            ]);

            $success = isset($refreshToken['body']['refresh_token']);
            $this->assertTrue($success);

        }
        

    }