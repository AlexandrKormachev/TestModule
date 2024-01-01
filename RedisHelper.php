<?php

namespace App\Helpers;


use Illuminate\Support\Env;
use Illuminate\Support\Facades\Redis;

class RedisHelper
{
    /**
     * Удаляет префиксы из переменных
     *
     * @param array|null $array массив ключей
     * @param string|null $parentKeys родительские ключи, если есть
     * @return Array|null
     */
    public function deletePrefix(?Array $array, ?string $parentKeys = null): ?Array
    {
        $prefix = config('app.redis_prefix').$parentKeys;
        foreach ($array as &$item)
        {
            $item = substr($item, strlen($prefix));
        }
        return $array;
    }

    /**
     * Удаляет переменные из Redis
     * учитывая префикс
     * @param string $variableWithMask
     * @return Int
     */
    public function deleteVariables(string $variableWithMask): Int
    {
      $findInRedis = Redis::keys($variableWithMask);
      if (empty($findInRedis)) {
          return 0;
      }
      return Redis::del($this->deletePrefix($findInRedis));
    }

    /**
     * Сохранение результатов запроса к БД
     * в кэш на определенное время
     * @param $data
     * @param int|null $timeoutToDeleteInSeconds
     * @param null|string $key
     * @return null|string
     */
    public function saveQueryResultToRedis($data, ?int $timeoutToDeleteInSeconds = null, ?string $key = '')
    {
        if (empty($key)) {
            $key = $this->generateKey();
        }
        if (empty($timeoutToDeleteInSeconds)) {
           Redis::set(':query:'.$key, json_encode($data, JSON_UNESCAPED_UNICODE));
        } else {
            Redis::setEx(':query:'.$key, $timeoutToDeleteInSeconds, json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        return $key;
    }

    /**
     * Генерация уникального ключа
     * @return string
     */
    public function generateKey()
    {
        $token = time();
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "0123456789";
        for ($i = 0; $i < 4; $i++) {
            $token .= $codeAlphabet[mt_rand(0, strlen($codeAlphabet) - 1)];
        }
        return md5($token);
    }
}
