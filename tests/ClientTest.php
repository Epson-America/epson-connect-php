<?php

namespace Epsonconnectphp\Epson\Tests;

use Epsonconnectphp\Epson\Client;
use Epsonconnectphp\Epson\ClientError;
use PHPUnit\Framework\TestCase;
use Mockery as Mockery;

class ClientTest extends TestCase
{
    private $client;
    public function setUp(): void
    {
              
    }

    public function testClientInitializationWithMissingPrinterEmail()
    {
        $this->expectException(ClientError::class);
        $this->client = new Client( null, "clientId", "clientSecret", "http://baseUrl");
    }

    public function testClientInitializationWithMissingClientId()
    {
        $this->expectException(ClientError::class);
        $this->client = new Client("printerEmail", null, "clientSecret", "http://baseUrl");
    }

    public function testClientInitializationWithMissingClientSecret()
    {
        $this->expectException(ClientError::class);
        $this->client = new Client("printerEmail", "clientId", null, "http://baseUrl");
    }

    // Fix Mocks
    // public function testGetPrinter()
    // {
    //     $authMock = Mockery::mock(Auth::class)->shouldIgnoreMissing();
    //     $authMock->shouldReceive('auth')->andReturnNull();  
    //     $this->client = new Client("printerEmail", "clientId", "clientSecret", "http://baseUrl", $authMock);
    //     $printer = $this->client->getPrinter();
    //     $this->assertNotNull($printer);
    // }

    // public function testGetScanner()
    // {
    //     $authMock = Mockery::mock(Auth::class)->shouldIgnoreMissing();
    //     $authMock->shouldReceive('auth')->andReturnNull();  
    //     $this->client = new Client("printerEmail", "clientId", "clientSecret", "http://baseUrl", $authMock);
    //     $scanner = $this->client->getScanner();
    //     $this->assertNotNull($scanner);
    // }
    public function testClientInitializationWithAllParams()
    {
        $authMock = Mockery::mock(Auth::class);
        $authMock->shouldReceive('auth')->andReturnNull();  
        $this->client = new Client("printerEmail", "clientId", "clientSecret", "http://baseUrl", $authMock);
        $this->assertNotNull($this->client);
    }
}