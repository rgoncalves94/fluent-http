<?php

namespace FluentHttp\Headers;
use FluentHttp\Headers\Header;

class DefaultHeader implements Header
{

    public function getHeader()
    {
        return [
            'Content-Type' => 'application/json',
            'Charset' => 'utf-8',
            'Accept' => 'application/json',
        ];
    }

}