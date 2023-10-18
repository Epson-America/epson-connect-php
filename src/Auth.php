<?php

namespace Epsonconnectphp\Epson;

/**
 * Auth class for Epson Connect PHP.
 *
 * This class handles the authentication process with the Epson Connect API.
 * It includes methods for sending HTTP requests, authenticating and deauthenticating the client,
 * and retrieving the device ID.
 *
 * @package Epsonconnectphp\Epson
 */
class Auth
{
    private $_client_id;
    private $_client_secret;
    private $_printer_email;
    public $_access_token = '';
    public $_refresh_token;
    public $_expires_at;
    public $_subject_id;
    private $_base_url;

    /**
     * Class constructor.
     *
     * @param string $base_url The base URL for the API.
     * @param string $printer_email The email of the printer.
     * @param string $client_id The client ID for the API.
     * @param string $client_secret The client secret for the API.
     */
    public function __construct($base_url, $printer_email, $client_id, $client_secret) {
        $this->_base_url = $base_url;
        $this->_printer_email = $printer_email;
        $this->_client_id = $client_id;
        $this->_client_secret = $client_secret;

        $this->_expires_at = new \DateTime();
        $this->_access_token = '';
        $this->_refresh_token = '';
        $this->_subject_id = '';

    }

    /**
     * Authenticates the client and retrieves the access token.
     *
     * @throws \Exception If an error occurs during authentication.
     */
    public function auth()
    {
        $method = 'POST';
        $path = '/api/1/printing/oauth2/auth/token?subject=printer';

        if ($this->_expires_at > new \DateTime()) {
            return;
        }
        $auth = "Basic " . base64_encode($this->_client_id . ':' . $this->_client_secret);


        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => $auth

        ];

        

        try {
            $body = $this->send($method, $path, $headers, $auth);
        } catch (\Exception $e) {
            throw new \Exception('AuthenticationError: ' . $e->getMessage());
        }

        $error = $body['error'] ?? null;
        if ($error) {
            throw new \Exception('AuthenticationError: ' . $error);
        }

        if ($this->_access_token == '') {
            $this->_refresh_token = $body['refresh_token'];
        }

        $this->_expires_at = new \DateTime('+' . $body['expires_in'] . ' seconds');
        $this->_access_token = $body['access_token'];
        $this->_subject_id = $body['subject_id'];
    }

    /**
     * Deauthenticates the client.
     */
    public function _deauthenticate()
    {
        $method = 'DELETE';
        $path = '/api/1/printing/printers/' . $this->_subject_id;
        $this->send($method, $path);
        $this->_access_token='';
    }

    /**
     * Sends an HTTP request.
     *
     * @param string $method The HTTP method.
     * @param string $path The path for the request.
     * @param array|null $headers The headers for the request.
     * @param mixed|null $data The data for the request.
     * @return array The response from the server.
     * @throws \Exception If an error occurs during the request.
     */
    public function send($method, $path, $headers = null, $data = null)
    {
        if($headers == null) {
            $headers = $this->default_headers();
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_base_url . $path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($this->_access_token == '') {
            $data = [
                'grant_type' => 'password',
                'username' => $this->_printer_email,
                'password' => '',
            ];
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            if ($data) {
                if (isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json') {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                } elseif (isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/octet-stream') {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                }
            }
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function($key, $value) {
            return "$key: $value";
        }, array_keys($headers), $headers));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('cURL Error: ' . curl_error($ch));
        }

        $resp = json_decode($result, true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $error = $resp['code'] ?? null;
        if ($error) {
            throw new \Exception('ApiError: ' . $error);
        }

        return $resp;
    }

    /**
     * Returns the default headers for requests.
     *
     * @return array The default headers.
     */
    public function default_headers()
    {
        return [
            'Authorization' => 'Bearer ' . $this->_access_token,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Returns the device ID.
     *
     * @return string The device ID.
     */
    public function device_id()
    {
        return $this->_subject_id;
    }
}