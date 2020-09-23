<?php

namespace app\controller;

use app\BaseController;

class Edit extends BaseController
{
    protected $redis;
    protected $database;
    protected $key;

    protected function initialize()
    {
        $this->redis = $this->request->param('redis', '');
        $this->database = $this->request->post('database', '');
        $this->key = $this->request->post('key', '');
        if ($this->redis === '' || $this->database === '' || $this->key === '') {
            $this->error();
        }
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

    protected function success($data = [], $msg = '操作成功', $code = 1)
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    protected function error($msg = '操作失败', $code = 0, $data = [])
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]);
    }
}
