<?php

    header('Content-Type: application/xml');
    

    echo toXml(array(
        'xml' => array(
            'status' => true,
            'message' => "It's a xml endpoint"
        )
    ));

    function toXml(array $data)
    {
        return recursive($data);
    }

    function recursive($data) 
    {
        $xml = "";
        foreach($data as $key => $value) {
            $xml .= sprintf("<%s>", $key);

            if(is_array($value) && !is_string($value))
                $xml .= recursive($value);
            else if(is_bool($value))
                $xml .= $value ? "true" : "false";
            else 
                $xml .= $value;

            $xml .= sprintf("</%s>", $key);
        }

        return trim($xml);
    }