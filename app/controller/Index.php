<?php

declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use app\library\RedisConfig;
use app\library\Redis;
use think\facade\View;
use think\Exception;

/**
 * Class Index
 * @package app\controller
 */
class Index extends BaseController
{
    /**
     * @return string
     * @throws Exception
     * @author WX
     * @datetime 2020/10/9 16:24
     */
    public function index()
    {
        $list = RedisConfig::getList();
        View::assign('list', $list);
        $redis = $this->request->param('redis');
        View::assign('redis', $redis);
        if ($redis) {
            $database = $this->request->param('database', '');
            $filter = $this->request->param('filter', '*');
            $filter = $filter ? $filter : '*';
            View::assign('database', $database);
            View::assign('filter', $filter);
            $keys = [];
            if (is_numeric($database)) {
                $keys = Redis::getKeys($redis, (int)$database, $filter);
            }
            View::assign('keys', $keys);

        } else {
            View::assign('database', '');
            View::assign('filter', '');
            View::assign('keys', []);
        }
        return View::fetch();
    }

    /**
     * @return string
     * @author WX
     * @datetime 2020/10/9 16:24
     */
    public function detail()
    {
        $redis = $this->request->param('redis');
        $database = $this->request->param('database', '');
        $key = $this->request->param('key', '');
        $type = '';
        $ttl = '';
        $re = '';
        $view = 'blank';
        if (is_numeric($database) && $key) {
            try {
                $client = Redis::getClient($redis);
                $client->select((int)$database);
                $type = $client->type($key);
                switch ($type) {
                    case 0:
                        $type = '';
                        $re = 'key不存在';
                        break;
                    case 3:
                        $type = 'list';
                        $view = 'list';
                        $re = $client->lrange($key, 0, -1);
                        break;
                    case 5:
                        $type = 'hash';
                        $view = 'hash';
                        $re = $client->hgetall($key);
                        break;
                    case 4:
                        $type = 'zset';
                        $view = 'zset';
                        $re = $client->zrange($key, 0, -1, true);
                        break;
                    case 2:
                        $type = 'set';
                        $view = 'set';
                        $re = $client->smembers($key);
                        break;
                    default:
                        $type = 'string';
                        $view = 'string';
                        $re = $client->get($key);
                        break;
                }
                if ($type) {
                    $ttl = $client->ttl($key);
                }
            } catch (Exception $e) {
                dump($e->getMessage());
            }
        }
        View::assign('key', $key);
        View::assign('type', $type);
        View::assign('ttl', $ttl);
        View::assign('re', $re);
        return View::fetch($view);
    }
}
