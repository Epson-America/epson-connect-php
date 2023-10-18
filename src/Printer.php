<?php

namespace Epsonconnectphp\Epson;

/**
 * Printer class for Epson Connect PHP.
 *
 * This class handles the interaction with the Epson Connect API for printing tasks.
 * It includes methods for uploading files, setting print settings, and executing print jobs.
 *
 * @package Epsonconnectphp\Epson
 */
class Printer
{
    private $_auth;
    private $_valid_extensions = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'jpeg', 'jpg', 'bmp', 'gif', 'png', 'tiff'];
    private $_valid_operators = ['user', 'operator'];

    /**
     * Constructor for the Printer class.
     *
     * @param Auth $auth An instance of the Auth class for authentication.
     */
    public function __construct(Auth $auth) {
        $this->_auth = $auth;
    }

    /**
     * Retrieves the device ID from the Auth instance.
     *
     * @return string The device ID.
     */
    public function getDeviceId() {
        return $this->_auth->device_id();
    }

    /**
     * Retrieves the capabilities of the printer.
     *
     * @param string $mode The mode for which to retrieve capabilities.
     * @return array The capabilities of the printer.
     */
    public function capabilities($mode) {
        $method = 'GET';
        $path = '/api/1/printing/printers/' . $this->getDeviceId() . '/capability/' . $mode;
        $result = $this->_auth->send($method, $path);
        return $result;
    }

    /**
     * Uploads a file to the specified URI for printing.
     *
     * @param string $upload_uri The URI to which to upload the file.
     * @param string $file_path The path of the file to upload.
     * @param string $print_mode The print mode (e.g., 'photo').
     * @return array The response from the API.
     * @throws \Exception If the file extension is not valid for printing.
     */
    public function upload_file($upload_uri, $file_path, $print_mode) {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->_valid_extensions)) {
            throw new \Exception($extension . ' is not a valid printing extension.');
        }
    
        $o = parse_url($upload_uri);
        parse_str($o['query'], $q_dict);
        $q_dict = [
            'Key' => $q_dict['Key'],
            'File' => '1.' . $extension,
        ];
        $o['query'] = http_build_query($q_dict);
        $path = $o['path'] . '?' . $o['query'];
    
        $content_type = 'application/octet-stream';
        if ($print_mode == 'photo') {
            $content_type = 'image/jpeg';
        }
    
        $data = file_get_contents($file_path);
    
        $headers = [
            'Content-Type' => $content_type,
            'Content-Length' => strlen($data),
            'Authorization' => $this->_auth->_access_token
        ];
    
        $method = 'POST';
        $resp = $this->_auth->send($method, $path, $headers, $data);
        return $resp;
    }

    /**
     * Merges the provided settings with the default settings.
     *
     * @param array $settings The settings to merge with the defaults.
     * @return array The merged settings.
     */
    public function mergeWithDefaultSettings($settings) {
        if ($settings === null) {
            $settings = [];
        }
    
        $jobName = $settings['job_name'] ?? '';
        if (empty($jobName)) {
            // Generate random name if one is not given.
            $jobName = 'job-' . bin2hex(random_bytes(4)); // generates a random 8-character string
        }
        $settings['job_name'] = $jobName;
        $settings['print_mode'] = $settings['print_mode'] ?? 'document';
    
        $printSetting = $settings['print_setting'] ?? [];
    
        if (empty($printSetting)) {
            return $settings;
        }
    
        $collate = $printSetting['collate'] ?? true;
    
        $mergedPrintSetting = [
            'media_size' => $printSetting['media_size'] ?? 'ms_a4',
            'media_type' => $printSetting['media_type'] ?? 'mt_plainpaper',
            'borderless' => $printSetting['borderless'] ?? false,
            'print_quality' => $printSetting['print_quality'] ?? 'normal',
            'source' => $printSetting['source'] ?? 'auto',
            'color_mode' => $printSetting['color_mode'] ?? 'color',
            '2_sided' => $printSetting['2_sided'] ?? 'none',
            'reverse_order' => $printSetting['reverse_order'] ?? false,
            'copies' => $printSetting['copies'] ?? 1,
            'collate' => $collate,
        ];
    
        $settings['print_setting'] = $mergedPrintSetting;
        return $settings;
    }

    /**
     * Sends the print settings to the API.
     *
     * @param array $settings The print settings to send.
     * @return array The response from the API.
     */
    public function print_setting($settings) {
        $method = 'POST';
        $path = '/api/1/printing/printers/' . $this->getDeviceId() . '/jobs';
        $settings = $this->mergeWithDefaultSettings($settings);
    
        // $this->validate_settings($settings);
    
        $resp = $this->_auth->send($method, $path, null, $settings);

        return $resp;
    }

    /**
     * Executes a print job with the specified job ID.
     *
     * @param string $jobId The ID of the job to print.
     */
    public function executePrint($jobId) {
        $method = 'POST';
        $path = '/api/1/printing/printers/' . $this->getDeviceId() . '/jobs/' . $jobId . '/print';
    
        $headers = [
            'Authorization' => 'Bearer ' . $this->_auth->_access_token,
        ];
    
        $this->_auth->send($method, $path, $headers);
    }

    /**
     * Prints a file.
     *
     * This method uploads the file, sends the print settings, and executes the print job.
     *
     * @param string $file_path The path of the file to print.
     * @return string The ID of the print job.
     */
    public function print($file_path) {
        $jobData = $this->print_setting(null);
        $this->upload_file($jobData["upload_uri"], $file_path, "document");
        $this->executePrint($jobData["id"]);
        return $jobData["id"];
    }
}