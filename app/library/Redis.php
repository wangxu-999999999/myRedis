<?php

namespace app\library;

use think\Exception;
use think\facade\Config;

class Redis
{
    // redis客户端实例
    private static $clients;

    /**
     * 获取redis客户端实例
     * @param string $redis
     * @return \Redis
     * @throws Exception
     * @author WX
     * @datetime 2020/9/23 14:39
     */
    public static function getClient($redis)
    {
        if (isset(self::$clients[$redis]) && (self::$clients[$redis] instanceof \Redis)) {
            return self::$clients[$redis];
        } else {
            $config = Config::get('redis.' . $redis);
            $client = new \Redis();
            if (!isset($config['host']) || !$config['host']) {
                throw new Exception('请先配置redis连接信息');
            }
            $host = $config['host'];
            $port = isset($config['port']) ? $config['port'] : 6379;
            $timeout = isset($config['timeout']) ? $config['timeout'] : 3;
            $client->connect($host, $port, $timeout);
            if (isset($config['auth']) && $config['auth']) {
                $client->auth($config['auth']);
            }
            self::$clients[$redis] = $client;
            return $client;
        }
    }

    /**
     * 获取keys
     * @param string $redis
     * @param int $database
     * @param string $filter
     * @return array
     * @throws Exception
     * @author WX
     * @datetime 2020/9/23 15:17
     */
    public static function getKeys($redis, $database, $filter = '*')
    {
        $client = self::getClient($redis);
        $client->select((int)$database);
        $config = Config::get('redis.' . $redis);
        if (isset($config['type']) && $config['type'] == 'cluster') {
            // 集群
            $nodes = $client->rawCommand('cluster', 'nodes');
            $nodes = self::getNodes($nodes);
            $keys = [];
            foreach ($nodes as $v) {
                $keys = array_merge($keys, $client->rawCommand('keys', $filter, $v));
            }
            sort($keys);
        } else {
            // 单机
            $keys = $client->keys($filter);
        }
        return $keys;
    }

    /**
     * 解析节点
     * @param string $str
     * @return array
     * @author WX
     * @datetime 2020/9/23 15:06
     */
    private static function getNodes($str)
    {
        $nodesArr = explode("\n", $str);
        $nodes = [];
        foreach ($nodesArr as $v) {
            if ($v && false !== stripos($v, 'master')) {
                $nodes[] = explode(' ', $v)[0];
            }
        }
        return $nodes;
    }
}
