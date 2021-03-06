<?php

namespace Islandora\Gemini\Tests;

use Islandora\Gemini\Controller\GeminiController;
use Islandora\Crayfish\Commons\PathMapper\PathMapper;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class CreatePairTest extends \PHPUnit_Framework_TestCase
{
    public function testReturns500OnException()
    {
        $prophecy = $this->prophesize(PathMapper::class);
        $prophecy->createPair(Argument::Any(), Argument::Any())
            ->willThrow(new \Exception("Exception", 500));
        $mock_service = $prophecy->reveal();
        $controller = new GeminiController($mock_service);

        $request = Request::create(
            "/",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"drupal" : "foo/bar", "fedora" : "baz/boo"}'
        );

        $response = $controller->createPair($request);

        $this->assertTrue(
            $response->getStatusCode() == 500,
            "Response must be 500 when Exception occurs"
        );
    }

    public function testReturns400OnMalformedRequest()
    {
        $prophecy = $this->prophesize(PathMapper::class);
        $mock_service = $prophecy->reveal();
        $controller = new GeminiController($mock_service);

        $response = $controller->createPair(Request::create(
            "/",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));

        $this->assertTrue(
            $response->getStatusCode() == 400,
            "Expected 400 if empty POST body"
        );

        $response = $controller->createPair(Request::create(
            "/",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"foo" : "bar"}'
        ));

        $this->assertTrue(
            $response->getStatusCode() == 400,
            "Expected 400 if POST contains neither Drupal nor Fedora uris"
        );

        $response = $controller->createPair(Request::create(
            "/",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"drupal" : "foo/bar"}'
        ));

        $this->assertTrue(
            $response->getStatusCode() == 400,
            "Expected 400 if POST does not contain Fedora uri"
        );

        $response = $controller->createPair(Request::create(
            "/",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"fedora" : "baz/boo"}'
        ));

        $this->assertTrue(
            $response->getStatusCode() == 400,
            "Expected 400 if POST does not contain Drupal uri"
        );
    }

    public function testReturns201OnCreation()
    {
        $prophecy = $this->prophesize(PathMapper::class);
        $mock_service = $prophecy->reveal();
        $controller = new GeminiController($mock_service);

        $request = Request::create(
            "/",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"drupal" : "foo/bar", "fedora" : "baz/boo"}'
        );

        $response = $controller->createPair($request);

        $this->assertTrue(
            $response->getStatusCode() == 201,
            "Response must be 201 on success"
        );
    }
}
