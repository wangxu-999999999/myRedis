<?php

declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use app\library\Redis;
use think\Exception;
use think\exception\HttpResponseException;
use think\Response;

/**
 * Class Edit
 * @package app\controller
 */
class Edit extends BaseController
{
    /**
     * @var \Redis
     */
    protected $client;

    /**
     * @var string
     */
    protected $key;

    /**
     * 初始化方法
     * @author WX
     * @datetime 2020/10/9 16:23
     */
    protected function initialize()
    {
        $redis = $this->request->post('redis', '');
        $database = $this->request->post('database', '');
        $this->key = $this->request->post('key', '');
        if ($redis === '' || !is_numeric($database) || $this->key === '') {
            $this->error();
        }
        try {
            $this->client = Redis::getClient($redis);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->client->select((int)$database);
    }

    /**
     * @author WX
     * @datetime 2020/10/9 16:19
     */
    public function del()
    {
        if ($this->client->del($this->key)) {
            $this->success();
        }
        $this->error();
    }

    /**
     * @author WX
     * @datetime 2020/10/9 16:19
     */
    public function string()
    {
        $string = $this->request->post('string', '');
        if ($this->client->set($this->key, $string)) {
            $this->success();
        }
        $this->error();
    }

    /**
     * @author WX
     * @datetime 2020/10/9 16:20
     */
    public function editList()
    {
        $k = $this->request->post('k', '');
        $string = $this->request->post('string', '');
        if (is_numeric($k) && $this->client->lSet($this->key, $k, $string)) {
            $this->success();
        }
        $this->error();
    }

    /**
     * @author WX
     * @datetime 2020/10/9 16:20
     */
    public function delList()
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

    /**
     * @author WX
     * @datetime 2020/10/9 16:20
     */
    public function editHash()
    {
        $k = (string)$this->request->post('k', '');
        $string = $this->request->post('string', '');
        if (($k !== '') && (false !== $this->client->hSet($this->key, $k, $string))) {
            $this->success();
        }
        $this->error();
    }

    /**
     * @author WX
     * @datetime 2020/10/9 16:20
     */
    public function delHash()
    {
        $k = $this->request->post('k', '');
        if (($k !== '') && ($this->client->hDel($this->key, $k))) {
            $this->success();
        }
        $this->error();
    }

    /**
     * @author WX
     * @datetime 2020/10/9 16:20
     */
    public function delSet()
    {
        $set = $this->request->post('set', '');
        if (($set !== '') && ($this->client->sRem($this->key, $set))) {
            $this->success();
        }
        $this->error();
    }

    /**
     * @author WX
     * @datetime 2020/10/9 16:20
     */
    public function editZset()
    {
        $k = $this->request->post('k', '');
        $score = $this->request->post('score', '');
        if (($k !== '') && is_numeric($score)) {
            $this->client->zAdd($this->key, ['XX'], $score, $k);
            $this->success();
        }
        $this->error();
    }

    /**
     * @author WX
     * @datetime 2020/10/9 16:20
     */
    public function delZset()
    {
        $k = $this->request->post('k', '');
        if (($k !== '') && $this->client->zRem($this->key, $k)) {
            $this->success();
        }
        $this->error();
    }

    /**
     * @author WX
     * @datetime 2020/10/9 16:20
     */
    public function ttl()
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

    /**
     * @param array $data
     * @param string $msg
     * @param int $code
     * @author WX
     * @datetime 2020/10/9 16:20
     */
    protected function success(array $data = [], string $msg = '操作成功', int $code = 1)
    {
        $this->result($code, $data, $msg);
    }

    /**
     * @param string $msg
     * @param int $code
     * @param array $data
     * @author WX
     * @datetime 2020/10/9 16:20
     */
    protected function error(string $msg = '操作失败', int $code = 0, array $data = [])
    {
        $this->result($code, $data, $msg);
    }

    /**
     * @param int $code
     * @param array $data
     * @param string $msg
     * @author WX
     * @datetime 2020/10/9 16:20
     */
    private function result(int $code, array $data, string $msg)
    {
        $data = [
            'code' => $code,
            'data' => $data,
            'msg' => $msg,
        ];
        throw new HttpResponseException(Response::create($data, 'json', 200));
    }
}
