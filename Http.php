<?php

namespace FluentHttp;

use FluentHttp\Headers\Header;
use FluentHttp\Headers\DefaultHeader;
use FluentHttp\Method;
use Closure;
use RuntimeException;


class Http
{
    private $url;
    private $method;
    private $data;
    private $behavior;
    private $headers;

    private $enabeDebug = false;

    public static function create(Header $headers = null)
    {
        if(empty($header))
            new DefaultHeader();

        return new Http($headers);
    }

    public function __construct(Header $header)
    {
        $this->headers = $header;
    }

    public function header(Header $header)
    {
        $this->header = $header;
    }

    public function get($url, $data = [], Closure $behavior = null)
    {
        if(!empty($data)) {
            $queryStr = http_build_query($data);

            if(!preg_match('/\?/', $url)) {
                $queryStr = '?' . $queryStr;
            } else if(!preg_match('/\?$/', $url)) {
                if(preg_match('/\&/', $url)) {
                    $queryStr = "&" . $queryStr;
                }
            }
            
            $url .= $queryStr;
        }
        
        $this->url = $url;
        $this->data = [];
        $this->method = Method::METHOD_GET;
        $this->behavior = $behavior;
                
        return $this;
    }

    public function post($url, $data, Closure $behavior = null)
    {
        $this->url = $url;
        $this->data = $data;
        $this->method = Method::METHOD_POST;
        $this->behavior = $behavior;
                
        return $this;
    }

    public function put($url, $data, Closure $behavior = null)
    {
        $this->url = $url;
        $this->data = $data;
        $this->method = Method::METHOD_PUT;
        $this->behavior = $behavior;
                
        return $this;
    }

    public function delete($url, $data, Closure $behavior = null)
    {
        $this->url = $url;
        $this->data = $data;
        $this->method = Method::METHOD_DELETE;
        $this->behavior = $behavior;
                
        return $this;
    }

    public function raw()
    {
        return $this->doRequest($this->url, $this->method, $this->data, $this->behavior);
    }

    public function subscribe(Closure $fn)
    {
        $fn($this->raw());

        return $this;
    }

    public function send()
    {
        $rawResponse = $this->raw();

        if(json_decode($rawResponse, 1) !== false)
            return json_decode($rawResponse, 1);

        if(json_decode(simplexml_load_string($rawResponse), 1) !== false)
            return json_decode(simplexml_load_string($rawResponse), 1);

        return html_entity_decode($rawResponse);
    }

    public function debug($status = true) {
        $this->enabeDebug = $status;

        return $this;
    }

    protected function doRequest($url, $method, $data, $behavior)
    {
        $ch = curl_init();
        
        curl_setopt($ch,CURLOPT_URL, $url); 
        curl_setopt($ch,CURLOPT_HTTPHEADER, $this->headers->getHeaders()); 
        curl_setopt($ch,CURLOPT_TIMEOUT, 60); 
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        //curl_setopt($ch,CURLOPT_MAXREDIRS, $this->_maxRedirects); 
        //curl_setopt($ch,CURLOPT_FOLLOWLOCATION,$this->_followlocation); 
        //curl_setopt($ch,CURLOPT_COOKIEJAR,$this->_cookieFileLocation); 
        //curl_setopt($ch,CURLOPT_COOKIEFILE,$this->_cookieFileLocation); 

        if($method != Method::METHOD_GET)
            if(!empty($data))
                curl_setopt($ch,CURLOPT_POSTFIELDS, $data); 

        switch($method) {
            case Method::METHOD_GET:
            break;
            case Method::METHOD_POST:
                curl_setopt($ch,CURLOPT_POST, true); 
            break;
            case Method::METHOD_PUT:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, Method::METHOD_PUT);
            break;
            case Method::METHOD_DELETE:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, Method::METHOD_DELETE);
            break;
            default:
                throw new RuntimeException('Invalid Http Verbose method');
        }

        if($this->enabeDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, 1);

        if(!empty($behavior))
            $behavior($ch, $data);
    }

    protected function isValidVerbose($method)
    {
        return in_array($method, [
            Method::METHOD_GET, Method::METHOD_POST, Method::METHOD_PUT, Method::METHOD_DELETE
        ]);
    }

    protected function isValidUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== FALSE;
    }

    protected function isValidData($data)
    {
        return is_array($data);
    }

}