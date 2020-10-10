<?php

declare (strict_types = 1);

namespace app\library;

use think\Exception;
use think\facade\Config;

/**
 * Class RedisConfig
 * @package app\library
 */
class RedisConfig
{
    /**
     * 获取redis配置列表
     * @return array
     * @throws Exception
     * @author WX
     * @datetime 2020/9/23 11:37
     */
    public static function getList()
    {
        $config = Config::get('redis');
        if (!is_array($config) || !$config) {
            throw new Exception('请先配置redis连接信息');
        }
        $list = [];
        foreach ($config as $k => $v) {
            $list[$k] = (isset($v['name']) && $v['name']) ? $v['name'] : $k;
        }
        return $list;
    }
}
