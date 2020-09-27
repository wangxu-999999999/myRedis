<?php

namespace app\controller;

use app\BaseController;
use app\library\Redis;

class Edit extends BaseController
{
    /**
     * @var \Redis
     */
    protected $client;
    protected $key;

    protected function initialize()
    {
        $redis = $this->request->post('redis', '');
        $database = $this->request->post('database', '');
        $this->key = $this->request->post('key', '');
        if ($redis === '' || !is_numeric($database) || $this->key === '') {
            $this->error();
        }
        $this->client = Redis::getClient($redis);
        $this->client->select((int)$database);
    }

    protected function del()
    {
        if ($this->client->del($this->key)) {
            $this->success();
        }
        $this->error();
    }

    protected function string()
    {
        $string = $this->request->post('string', '');
        if ($this->client->set($this->key, $string)) {
            $this->success();
        }
        $this->error();
    }

    protected function editList()
    {
        $k = $this->request->post('k', '');
        $string = $this->request->post('string', '');
        if (is_numeric($k) && $this->client->lSet($this->key, $k, $string)) {
            $this->success();
        }
        $this->error();
    }

    protected function delList()
    {
        $k = $this->request->post('k', '');
        if (is_numeric($k)) {
            $string = '---delete---';
            $this->client->lSet($this->key, $k, $string);
            if ($this->client->lRem($this->key, $string, 0)) {
                $this->success();
            }
        }
        $this->error();
    }

    protected function editHash()
    {
        $k = $this->request->post('k', '');
        $string = $this->request->post('string', '');
        if (($k !== '') && (false !== $this->client->hSet($this->key, $k, $string))) {
            $this->success();
        }
        $this->error();
    }

    protected function delHash()
    {
        $k = $this->request->post('k', '');
        if (($k !== '') && ($this->client->hDel($this->key, $k))) {
            $this->success();
        }
        $this->error();
    }

    protected function delSet()
    {
        $set = $this->request->post('set', '');
        if (($set !== '') && ($this->client->sRem($this->key, $set))) {
            $this->success();
        }
        $this->error();
    }

    protected function editZset()
    {
        $k = $this->request->post('k', '');
        $score = $this->request->post('score', '');
        if (($k !== '') && is_numeric($score) && ($this->client->zAdd($this->key, ['XX'], $score, $k))) {
            $this->success();
        }
        $this->error();
    }

    protected function delZset()
    {
        $k = $this->request->post('k', '');
        if (($k !== '') && $this->client->zRem($this->key, $k)) {
            $this->success();
        }
        $this->error();
    }

    protected function ttl()
    {
        $ttl = (int)$this->request->post('ttl', -1);
        if (is_numeric($ttl)) {
            if ($ttl <= 0) {
                $re = $this->client->persist($this->key);
            } else {
                $re = $this->client->expire($this->key, $ttl);
            }
            if ($re) {
                $this->success();
            }
        }
        $this->error();
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
