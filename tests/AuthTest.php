<?php

use PHPUnit\Framework\TestCase;
use Epsonconnectphp\Epson\Auth;
use Mockery as Mockery;

class AuthTest extends TestCase
{
    private $auth;

    public function setUp(): void
    {
        $this->auth = Mockery::mock(Auth::class)->makePartial();
        $this->auth->shouldReceive('send')->andReturn([
            'refresh_token' => 'rf-123',
            'expires_in' => '3600',
            'access_token' => 'at-5678',
            'subject_id' => 'test_subj_id',
        ]);
    }

    public function testAuth()
    {
        $this->auth->auth();

        $this->assertNotEmpty($this->auth->_access_token);
        $this->assertNotEmpty($this->auth->_refresh_token);
        $this->assertNotEmpty($this->auth->_subject_id);
    }

    public function testDeauthenticate()
    {
        $this->auth->auth();
        $this->auth->_deauthenticate();

        $this->assertEmpty($this->auth->_access_token);
    }

    public function testDefaultHeaders()
    {
        $this->auth->auth();
        $headers = $this->auth->default_headers();

        $this->assertIsArray($headers);
        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertEquals('Bearer ' . $this->auth->_access_token, $headers['Authorization']);
        $this->assertEquals('application/json', $headers['Content-Type']);
    }

    public function testDeviceId()
    {
        $this->auth->auth();
        $deviceId = $this->auth->device_id();

        $this->assertNotEmpty($deviceId);
        $this->assertEquals($this->auth->_subject_id, $deviceId);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}