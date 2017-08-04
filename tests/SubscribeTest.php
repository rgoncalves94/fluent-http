<?php

namespace Tests\FluentHttp;

use PHPUnit\Framework\TestCase;
use FluentHttp\Http;
use FluentHttp\Headers\HeaderFactory;
use ReflectionClass;

/**
 * @covers FluentHttp\Http;
 */
final class SubscribeTest extends TestCase
{

    public function testReceivingDataParameter()
    {
        $response = Http::create()
            ->get(TestUrls::JSON_URL)
            ->subscribe(function($data) {
                return $data;
            });

        $this->assertJsonStringEqualsJsonString('{
  "userId": 1,
  "id": 1,
  "title": "sunt aut facere repellat provident occaecati excepturi optio reprehenderit",
  "body": "quia et suscipit\nsuscipit recusandae consequuntur expedita et cum\nreprehenderit molestiae ut ut quas totam\nnostrum rerum est autem sunt rem eveniet architecto"
}', 
$response);
    }
    
    /**
     * @depends testReceivingDataParameter
     */
    public function testReceivingDataAndHttpParameter()
    {
        $http = Http::create();
        $response = $http
            ->get(TestUrls::JSON_URL)
            ->subscribe(function($data, $http) {
                return compact('data', 'http');
            });

        $this->assertArrayHasKey("http", $response);
        $this->assertArrayHasKey("data", $response);
        $this->assertSame($response['http'], $http);
    }

    /**
     * @depends testReceivingDataParameter
     */
    public function testReceivingDataHttpAndJsonParameter()
    {
        $http = Http::create();
        $response = $http
            ->get(TestUrls::JSON_URL)
            ->subscribe(function($json, $data, $http) {
                return compact('data', 'http', 'json');
            });

        $this->assertArrayHasKey("http", $response);
        $this->assertArrayHasKey("data", $response);
        $this->assertArrayHasKey("json", $response);
        $this->assertSame($response['http'], $http);
        $this->assertEquals(array(
            "status" => true, 
            "message" => "It's a json endpoint"
        ), $response['json']);
    }

    /**
     * @depends testReceivingDataParameter
     */
    public function testReceivingDataHttpAndXMLParameter()
    {
        $http = Http::create();
        $response = $http
            ->get(TestUrls::XML_URL)
            ->subscribe(function($data, $http, $xml) {
                return compact('data', 'http', 'xml');
            });

        $this->assertArrayHasKey("http", $response);
        $this->assertArrayHasKey("data", $response);
        $this->assertArrayHasKey("xml", $response);
        $this->assertSame($response['http'], $http);
        $this->assertEquals(array(
            "status" => "true", 
            "message" => "It's a xml endpoint"
        ), $response['xml']);
    }

};