<?php

$app['global_middlewares'] = [
    'CheckMaintananceMode',

    'RestMiddleware'
];


//register middleware with name
$app['middlewares'] = [
    'auth' => 'AuthMiddleware'
];




$app['after_middlewares'] = [];

return $app;
