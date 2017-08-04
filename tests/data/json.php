<?php

    header('Content-Type: application/json');

    echo json_encode(array(
        'status' => true,
        'message' => "It's a json endpoint"
    ));