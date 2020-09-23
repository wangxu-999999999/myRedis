<?php

namespace app\controller;

use app\BaseController;
use app\library\RedisConfig;
use app\library\Redis;
use think\facade\View;

class Index extends BaseController
{
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
                $keys = Redis::getKeys($redis, $database, $filter);
            }
            View::assign('keys', $keys);

        } else {
            View::assign('database', '');
            View::assign('filter', '');
            View::assign('keys', []);
        }
        return View::fetch();
    }

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
            } catch (\Exception $e) {
                dump($e->getMessage());
            }
        }
        View::assign('key', $key);
        View::assign('type', $type);
        View::assign('ttl', $ttl);
        View::assign('re', $re);
        return View::fetch($view);
    }

    public function del()
    {
        $return = [
            'code' => 0,
            'msg' => '操作失败',
        ];
        $redis = $this->request->param('redis');
        $database = $this->request->post('database', '');
        $key = $this->request->post('key', '');
        if (is_numeric($database) && $key !== '') {
            try {
                $client = Redis::getClient($redis);
                $client->select((int)$database);
                $client->del($key);
                $return = [
                    'code' => 1,
                    'msg' => '操作成功',
                ];
            } catch (\Exception $e) {
                $return['msg'] = $e->getMessage();
            }
        }
        return json($return);
    }

    public function string()
    {
        $return = [
            'code' => 0,
            'msg' => '操作失败',
        ];
        $redis = $this->request->param('redis');
        $database = $this->request->post('database', '');
        $key = $this->request->post('key', '');
        $string = $this->request->post('string', '');
        if (is_numeric($database) && $key !== '') {
            try {
                $client = Redis::getClient($redis);
                $client->select((int)$database);
                if ($client->set($key, $string)) {
                    $return = [
                        'code' => 1,
                        'msg' => '操作成功',
                    ];
                }
            } catch (\Exception $e) {
                $return['msg'] = $e->getMessage();
            }
        }
        return json($return);
    }

    public function editList()
    {
        $return = [
            'code' => 0,
            'msg' => '操作失败',
        ];
        $redis = $this->request->param('redis');
        $database = $this->request->post('database', '');
        $key = $this->request->post('key', '');
        $k = $this->request->post('k', '');
        $string = $this->request->post('string', '');
        if (is_numeric($database) && $key !== '' && is_numeric($k)) {
            try {
                $client = Redis::getClient($redis);
                $client->select((int)$database);
                if ($client->lSet($key, $k, $string)) {
                    $return = [
                        'code' => 1,
                        'msg' => '操作成功',
                    ];
                }
            } catch (\Exception $e) {
                $return['msg'] = $e->getMessage();
            }
        }
        return json($return);
    }

    public function delList()
    {
        $return = [
            'code' => 0,
            'msg' => '操作失败',
        ];
        $redis = $this->request->param('redis');
        $database = $this->request->post('database', '');
        $key = $this->request->post('key', '');
        $k = $this->request->post('k', '');
        if (is_numeric($database) && $key !== '' && is_numeric($k)) {
            try {
                $client = Redis::getClient($redis);
                $client->select((int)$database);
                $string = '---delete---';
                $client->lSet($key, $k, $string);
                if ($client->lRem($key, $string, 0)) {
                    $return = [
                        'code' => 1,
                        'msg' => '操作成功',
                    ];
                }
            } catch (\Exception $e) {
                $return['msg'] = $e->getMessage();
            }
        }
        return json($return);
    }

    public function editHash()
    {
        $return = [
            'code' => 0,
            'msg' => '操作失败',
        ];
        $redis = $this->request->param('redis');
        $database = $this->request->post('database', '');
        $key = $this->request->post('key', '');
        $k = $this->request->post('k', '');
        $string = $this->request->post('string', '');
        if (is_numeric($database) && $key !== '' && $k !== '') {
            try {
                $client = Redis::getClient($redis);
                $client->select((int)$database);
                if (false !== $client->hSet($key, $k, $string)) {
                    $return = [
                        'code' => 1,
                        'msg' => '操作成功',
                    ];
                }
            } catch (\Exception $e) {
                $return['msg'] = $e->getMessage();
            }
        }
        return json($return);
    }

    public function delHash()
    {
        $return = [
            'code' => 0,
            'msg' => '操作失败',
        ];
        $redis = $this->request->param('redis');
        $database = $this->request->post('database', '');
        $key = $this->request->post('key', '');
        $k = $this->request->post('k', '');
        if (is_numeric($database) && $key !== '' && $k !== '') {
            try {
                $client = Redis::getClient($redis);
                $client->select((int)$database);
                if ($client->hDel($key, $k)) {
                    $return = [
                        'code' => 1,
                        'msg' => '操作成功',
                    ];
                }
            } catch (\Exception $e) {
                $return['msg'] = $e->getMessage();
            }
        }
        return json($return);
    }

    public function delSet()
    {
        $return = [
            'code' => 0,
            'msg' => '操作失败',
        ];
        $redis = $this->request->param('redis');
        $database = $this->request->post('database', '');
        $key = $this->request->post('key', '');
        $set = $this->request->post('set', '');
        if (is_numeric($database) && $key !== '' && $set !== '') {
            try {
                $client = Redis::getClient($redis);
                $client->select((int)$database);
                if ($client->sRem($key, $set)) {
                    $return = [
                        'code' => 1,
                        'msg' => '操作成功',
                    ];
                }
            } catch (\Exception $e) {
                $return['msg'] = $e->getMessage();
            }
        }
        return json($return);
    }

    public function editZset()
    {
        $return = [
            'code' => 0,
            'msg' => '操作失败',
        ];
        $redis = $this->request->param('redis');
        $database = $this->request->post('database', '');
        $key = $this->request->post('key', '');
        $k = $this->request->post('k', '');
        $score = $this->request->post('score', '');
        if (is_numeric($database) && $key !== '' && $k !== '') {
            try {
                $client = Redis::getClient($redis);
                $client->select((int)$database);
                $client->zAdd($key, ['XX'], $score, $k);
                $return = [
                    'code' => 1,
                    'msg' => '操作成功',
                ];
            } catch (\Exception $e) {
                $return['msg'] = $e->getMessage();
            }
        }
        return json($return);
    }

    public function delZset()
    {
        $return = [
            'code' => 0,
            'msg' => '操作失败',
        ];
        $redis = $this->request->param('redis');
        $database = $this->request->post('database', '');
        $key = $this->request->post('key', '');
        $k = $this->request->post('k', '');
        if (is_numeric($database) && $key !== '' && $k !== '') {
            try {
                $client = Redis::getClient($redis);
                $client->select((int)$database);
                $client->zRem($key, $k);
                $return = [
                    'code' => 1,
                    'msg' => '操作成功',
                ];
            } catch (\Exception $e) {
                $return['msg'] = $e->getMessage();
            }
        }
        return json($return);
    }

    public function ttl()
    {
        $return = [
            'code' => 0,
            'msg' => '操作失败',
        ];
        $redis = $this->request->param('redis');
        $database = $this->request->post('database', '');
        $key = $this->request->post('key', '');
        $ttl = $this->request->post('ttl', -1);
        if (is_numeric($database) && $key !== '' && is_numeric($ttl)) {
            try {
                $client = Redis::getClient($redis);
                $client->select((int)$database);
                $ttl = (int)$ttl;
                if ($ttl <= 0) {
                    $client->persist($key);
                } else {
                    $client->expire($key, $ttl);
                }
                $return = [
                    'code' => 1,
                    'msg' => '操作成功',
                ];
            } catch (\Exception $e) {
                $return['msg'] = $e->getMessage();
            }
        }
        return json($return);
    }
}
