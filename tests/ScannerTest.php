<?php

namespace Epsonconnectphp\Epson\Tests;

use Epsonconnectphp\Epson\Auth;
use Epsonconnectphp\Epson\Scanner;
use PHPUnit\Framework\TestCase;
use Mockery as Mockery;

class ScannerTest extends TestCase
{
    private $authMock;
    private $scanner;

    public function setUp(): void
    {
        $this->authMock = Mockery::mock(Auth::class);
        $this->authMock->shouldReceive('device_id')->andReturn([
            'setting' => 'testSetting',
        ]);

        $this->authMock->shouldReceive('send')->andReturn([
            'id' => 'mockedId',
            'alias_name' => 'mockedName',
            'type' => 'mockedType',
            'destination' => 'mockedDestination',
        ]);
    }

    public function testList()
    {
        $this->scanner = new Scanner($this->authMock);

        $result = $this->scanner->list();

        $this->assertEquals('mockedId', $result['id']);
        $this->authMock->shouldHaveReceived('send')->once();
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}