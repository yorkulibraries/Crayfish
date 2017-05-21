<?php

namespace Islandora\Lameduck\Tests;

use Islandora\Crayfish\Commons\CmdExecuteService;
use Islandora\Lameduck\Controller\LameduckController;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Request;
use Monolog\Logger;

class LameduckControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsFedoraError()
    {
        // Mock a CmdExecuteService to create a controller.
        $prophecy = $this->prophesize(CmdExecuteService::class);
        $mock_service = $prophecy->reveal();
        $controller = new LameduckController(
            $mock_service,
            'lame',
            $this->prophesize(Logger::class)->reveal()
        );

        // Mock a Fedora response.
        $prophecy = $this->prophesize(ResponseInterface::class);
        $prophecy->getBody()->willReturn();
        $prophecy->getHeaders()->willReturn();
        $prophecy->getStatusCode()->willReturn(401);
        $prophecy->getReasonPhrase()->willReturn("Unauthorized");
        $mock_fedora_response = $prophecy->reveal();

        // Create a Request.
        $request = Request::create(
            "/foo",
            "GET"
        );

        // Test lame
        $response = $controller->lame($mock_fedora_response, $request);

        $this->assertTrue(
            $response->getStatusCode() == 401,
            "Response code must be Fedora response code"
        );
        $this->assertTrue(
            $response->getContent() == "Unauthorized",
            "Response must return Fedora's reason phrase"
        );
    }

    public function testErrorReturns500()
    {
        // Mock a CmdExecuteService to create a controller.
        $prophecy = $this->prophesize(CmdExecuteService::class);
        $prophecy->execute(Argument::any(), Argument::any())->willThrow(new \RuntimeException("ERROR", 500));
        $mock_service = $prophecy->reveal();
        $controller = new LameduckController(
            $mock_service,
            'lame',
            $this->prophesize(Logger::class)->reveal()
        );

        // Mock a stream body for a Fedora response.
        $prophecy = $this->prophesize(StreamInterface::class);
        $prophecy->isReadable()->willReturn(true);
        $prophecy->isWritable()->willReturn(false);
        $mock_stream = $prophecy->reveal();

        // Mock a Fedora response.
        $prophecy = $this->prophesize(ResponseInterface::class);
        $prophecy->getStatusCode()->willReturn(200);
        $prophecy->getBody()->willReturn($mock_stream);
        $mock_fedora_response = $prophecy->reveal();

        // Create a Request.
        $request = Request::create(
            "/foo",
            "GET"
        );

        // Test lame
        $response = $controller->lame($mock_fedora_response, $request);
        $this->assertTrue($response->getStatusCode() == 500, "Response must return 500");
        $this->assertTrue($response->getContent() == "ERROR", "Response must return exception's message");
    }

    public function testSuccessReturns200()
    {
        // Mock a CmdExecuteService to create a controller.
        $prophecy = $this->prophesize(CmdExecuteService::class);
        $mock_service = $prophecy->reveal();
        $controller = new LameduckController(
            $mock_service,
            'lame',
            $this->prophesize(Logger::class)->reveal()
        );

        $request = Request::create(
            "/foo",
            "GET"
        );

        // Mock a stream body for a Fedora response.
        $prophecy = $this->prophesize(StreamInterface::class);
        $prophecy->isReadable()->willReturn(true);
        $prophecy->isWritable()->willReturn(false);
        $mock_stream = $prophecy->reveal();

        // Mock a Fedora response.
        $prophecy = $this->prophesize(ResponseInterface::class);
        $prophecy->getStatusCode()->willReturn(200);
        $prophecy->getBody()->willReturn($mock_stream);
        $mock_fedora_response = $prophecy->reveal();

        $response = $controller->lame($mock_fedora_response, $request);
        $this->assertTrue($response->getStatusCode() == 200, "Response must return 200");
    }
}
