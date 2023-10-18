<?php

namespace Epsonconnectphp\Epson\Tests;

use Epsonconnectphp\Epson\Auth;
use Epsonconnectphp\Epson\Printer;
use PHPUnit\Framework\TestCase;
use Mockery as Mockery;

class PrinterTest extends TestCase
{
    private $authMock;
    private $printer;

    public function setUp(): void
    {
        $this->authMock = Mockery::mock(Auth::class);
        $this->printer = new Printer($this->authMock);
    }

    // public function testJobInfo()
    // {
    //     $this->authMock->shouldReceive('send')->andReturn([
    //         'jobId' => 'testJobId',
    //         'status' => 'completed',
    //     ]);

    //     $result = $this->printer->jobInfo('testJobId');

    //     $this->assertEquals('testJobId', $result['jobId']);
    //     $this->assertEquals('completed', $result['status']);
    // }

    public function testPrintSetting()
    {
        $this->authMock->shouldReceive('send')->andReturn([
            'setting' => 'testSetting',
        ]);
        $this->authMock->shouldReceive('device_id')->andReturn([
            'setting' => 'testSetting',
        ]);


        $result = $this->printer->print_setting(null);

        $this->assertEquals('testSetting', $result['setting']);
    }

    public function testExecutePrint()
    {
        $this->authMock->shouldReceive('send')->andReturnNull();
        $this->authMock->shouldReceive('device_id')->andReturn([
            'setting' => 'testSetting',
        ]);


        $this->printer->executePrint('testJobId');

        $this->authMock->shouldHaveReceived('send')->once();
    }

    public function testPrint()
    {

        $this->authMock->shouldReceive('device_id')->andReturn([
            'setting' => 'testSetting',
        ]);

        $this->authMock->shouldReceive('send')->andReturn([
            'upload_uri' => 'https://baseUrl',
            'id' => 'testJobId',
        ]);

        $result = $this->printer->print('testFilePath.pdf');

        $this->assertEquals('testJobId', $result);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}