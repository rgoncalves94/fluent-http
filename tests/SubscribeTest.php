<?php

namespace Tests\FluentHttp;

use PHPUnit\Framework\TestCase;
use FluentHttp\Http;
use FluentHttp\Headers\HeaderFactory;
use ReflectionClass;

/**
 * @covers Http
 */
final class SubscribeTest extends TestCase
{

    const XML_URL = "http://localhost:3000/tests/data/xml.php";
    const JSON_URL = "http://localhost:3000/tests/data/json.php";
    const HTML_URL = "http://localhost:3000/tests/data/html.php";

    public function testReceivingDataParameter()
    {
        $response = Http::create()
            ->get(self::JSON_URL)
            ->subscribe(function($data) {
                return $data;
            });

        $this->assertJsonStringEqualsJsonString(json_encode(
            array(
                "status" => true, 
                "message" => "It's a json endpoint"
            )
        ), $response);
    }
    
    /**
     * @depends testReceivingDataParameter
     */
    public function testReceivingDataAndHttpParameter()
    {
        $http = Http::create();
        $response = $http
            ->get(self::JSON_URL)
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
            ->get(self::JSON_URL)
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
            ->get(self::XML_URL)
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