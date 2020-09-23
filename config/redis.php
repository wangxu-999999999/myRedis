<?php
// +----------------------------------------------------------------------
// | redis配置
// +----------------------------------------------------------------------

return [
    'redis_cluster' => [
        // 名称
        'name'      =>  env('redis_cluster.name'),
        // 类型，cluster，alone
        'type'      =>  env('redis_cluster.type'),
        'host'      =>  env('redis_cluster.host'),
        'port'      =>  env('redis_cluster.port', 6379),
        'auth'      =>  env('redis_cluster.auth', ''),
        'timeout'   =>  env('redis_cluster.timeout', 3),
    ],
    'redis_alone' => [
        'name'      =>  env('redis_alone.name'),
        'type'      =>  env('redis_alone.type'),
        'host'      =>  env('redis_alone.host'),
        'port'      =>  env('redis_alone.port', 6379),
        'auth'      =>  env('redis_alone.auth', ''),
        'timeout'   =>  env('redis_alone.timeout', 3),
    ],
];
