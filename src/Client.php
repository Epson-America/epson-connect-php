<?php

namespace Epsonconnectphp\Epson;

class Client
{
    /**
     * Client for the Epson Connect API.
     *
     * This class provides a higher-level interface to interact with the Epson Connect services,
     * including authentication, printer management, and scanning operations.
     */
    const EC_BASE_URL = 'https://api.epsonconnect.com';

    private $_auth_ctx;

    public function __construct($printer_email = '', $client_id = '', $client_secret = '', $base_url = '', $auth = null)
    {
        /**
         * Initialize the Epson Connect client.
         *
         * @param string $base_url The base URL for the Epson Connect API. Defaults to the official Epson Connect API URL.
         * @param string $printer_email Email of the printer used for authentication.
         * @param string $client_id OAuth client ID for the Epson Connect API.
         * @param string $client_secret OAuth client secret for the Epson Connect API.
         *
         * If any of the parameters are not provided, the method will attempt to fetch them from environment variables.
         */
        $base_url = $base_url ?: self::EC_BASE_URL;

        $printer_email = $printer_email ?: getenv('EPSON_CONNECT_API_PRINTER_EMAIL');
        if (!$printer_email) {
            throw new ClientError('Printer Email can not be empty');
        }

        $client_id = $client_id ?: getenv('EPSON_CONNECT_API_CLIENT_ID');
        if (!$client_id) {
            throw new ClientError('Client ID can not be empty');
        }

        $client_secret = $client_secret ?: getenv('EPSON_CONNECT_API_CLIENT_SECRET');
        if (!$client_secret) {
            throw new ClientError('Client Secret can not be empty');
        }

        $this->_auth_ctx = $auth ?: new Auth($base_url, $printer_email, $client_id, $client_secret);
        $this->_auth_ctx->auth();
    }

    public function deauthenticate()
    {
        /**
         * De-authenticate from the Epson Connect API.
         *
         * This method ends the current session and invalidates the access token.
         */
        $this->_auth_ctx->_deauthenticate();
    }

    public function getPrinter()
    {
        /**
         * Get the Printer interface for the current session.
         *
         * @return Printer An instance of the Printer class.
         */
        return new Printer($this->_auth_ctx);
    }

    public function getScanner()
    {
        /**
         * Get the Scanner interface for the current session.
         *
         * @return Scanner An instance of the Scanner class.
         */
        return new Scanner($this->_auth_ctx);
    }
}

class ClientError extends \Exception
{
    /**
     * Error raised for client-specific exceptions.
     *
     * This includes cases like missing credentials, misconfigured environment, etc.
     */
}