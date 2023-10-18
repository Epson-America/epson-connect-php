<?php

namespace Epsonconnectphp\Epson;

/**
 * Scanner class for Epson Connect PHP.
 *
 * This class handles the interaction with the Epson Connect API for scanning tasks.
 * It includes methods for listing, adding, updating, and removing scan destinations.
 *
 * @package Epsonconnectphp\Epson
 */
class Scanner
{
    private $_auth;
    private $_path;
    private $_destination_cache = [];

    const VALID_DESTINATION_TYPES = ['mail', 'url'];

    /**
     * Constructor for the Scanner class.
     *
     * @param Auth $auth An instance of the Auth class for authentication.
     */
    public function __construct(Auth $auth) {
        $this->_auth = $auth;
        $this->_path = '/api/1/scanning/scanners/' . $this->_auth->device_id() . '/destinations';
    }

    /**
     * Lists all scan destinations.
     *
     * @return array The list of scan destinations.
     */
    public function list()
    {
        $method = 'GET';
        $result =  $this->_auth->send($method, $this->_path);
        return $result;
    }

    /**
     * Adds a new scan destination.
     *
     * @param string $name The name of the scan destination.
     * @param string $destination The destination of the scan.
     * @param string $type The type of the scan destination. Default is 'mail'.
     * @return array The response from the API.
     * @throws \Exception If the destination name or destination is too long, or if the type is invalid.
     */
    public function add($name, $destination, $type = 'mail')
    {
        $method = 'POST';

        $this->_validate_destination($name, $destination, $type);
    
        $data = [
            'alias_name' => $name,
            'type' => $type,
            'destination' => $destination,
        ];
    
    
        $resp = $this->_auth->send($method, $this->_path, null, $data);
    
        return $resp;
    }

    /**
     * Updates an existing scan destination.
     *
     * @param string $id The ID of the scan destination to update.
     * @param string|null $name The new name of the scan destination. If null, the existing name is used.
     * @param string|null $destination The new destination of the scan. If null, the existing destination is used.
     * @param string|null $type The new type of the scan destination. If null, the existing type is used.
     * @return array The response from the API.
     * @throws \Exception If the destination is not registered, or if the destination name or destination is too long, or if the type is invalid.
     */
    public function update($id, $name = null, $destination = null, $type = null)
    {
        $method = 'POST';

        if (!isset($this->_destination_cache[$id])) {
            throw new \Exception('Scan destination is not yet registered.');
        }

        $this->_validate_destination($name, $destination, $type);

        $data = [
            'id' => $id,
            'alias_name' => $name ?? $this->_destination_cache[$id]['alias_name'],
            'type' => $type ?? $this->_destination_cache[$id]['type'],
            'destination' => $destination ?? $this->_destination_cache[$id]['destination'],
        ];

        $resp = $this->_auth->send($method, $this->_path, $data);
        $this->_destination_cache[$id] = $resp;
        return $resp;
    }

    /**
     * Removes a scan destination.
     *
     * @param string $id The ID of the scan destination to remove.
     */
    public function remove($id)
    {
        $method = 'DELETE';

        $data = [
            'id' => $id,
        ];

        $this->_auth->send($method, $this->_path, $data);
        unset($this->_destination_cache[$id]);
    }

    /**
     * Validates a scan destination.
     *
     * @param string $name The name of the scan destination.
     * @param string $destination The destination of the scan.
     * @param string $type The type of the scan destination.
     * @throws \Exception If the destination name or destination is too long, or if the type is invalid.
     */
    private function _validate_destination($name, $destination, $type)
    {
        if (strlen($name) < 1 || strlen($name) > 32) {
            throw new \Exception('Scan destination name too long.');
        }

        if (strlen($destination) < 4 || strlen($destination) > 544) {
            throw new \Exception('Scan destination too long.');
        }

        if (!in_array($type, self::VALID_DESTINATION_TYPES)) {
            throw new \Exception('Invalid scan destination type ' . $type . '.');
        }
    }
}