<?php
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Response;

/**
 * Created by PhpStorm.
 * User: Glenn
 * Date: 2015-11-12
 * Time: 1:19 PM
 */
class PhpRendererTest extends \PHPUnit_Framework_TestCase
{

    public function testRenderer() {
        $renderer = new \Slim\Views\PhpRenderer("tests/");

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render($response, "testTemplate.php", array("hello" => "Hi"));

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }
}
