<?php

namespace App\Services;

/**
 * Class SignGenerator
 * @package App\Services
 */
class SignGenerator
{
    CONST BITS_FULL = 64;
    CONST BITS_PRE = 1;//固定
    CONST BITS_TIME = 41;//毫秒时间戳 可以最多支持69年
    CONST BITS_SERVER = 5; //服务器最多支持32台
    CONST BITS_WORKER = 5; //最多支持32种业务
    CONST BITS_SEQUENCE = 12; //一毫秒内支持4096个请求

    CONST OFFSET_TIME = "2019-05-05 00:00:00";//时间戳起点时间

    /**
     * 服务器id
     */
    protected $serverId;

    /**
     * 业务id
     */
    protected $workerId;

    /**
     * 实例
     */
    protected static $instance;

    /**
     * redis 服务
     */
    protected static $redis;


    /**
     * @param $redis
     * @return SignGenerator
     * @throws \Exception
     */
    public static function getInstance($redis)
    {
        if (isset(self::$instance)) {
            return self::$instance;
        } else {
            return self::$instance = new self($redis);
        }
    }

    /**
     * SignGenerator constructor.
     * @param $redis
     * @throws \Exception
     */
    protected function __construct($redis)
    {
        if ($redis instanceof \Redis || $redis instanceof \Predis\Client) {
            self::$redis = $redis;
        } else {
            throw new \Exception("redis service is lost");
        }
    }

    /**
     * @return int|mixed
     * @throws \ErrorException
     */
    public function getNumber()
    {
        if (!isset($this->serverId)) {
            throw new \Exception("serverId is lost");
        }
        if (!isset($this->workerId)) {
            throw new \Exception("workerId is lost");
        }

        do {
            $id = pow(2, self::BITS_FULL - self::BITS_PRE) << self::BITS_PRE;
            //时间戳 41位
            $nowTime = (int)(microtime(true) * 1000);
            $startTime = (int)(strtotime(self::OFFSET_TIME) * 1000);
            $diffTime = $nowTime - $startTime;
            $shift = self::BITS_FULL - self::BITS_PRE - self::BITS_TIME;
            $id |= $diffTime << $shift;
            echo "diffTime=", $diffTime, "\t";

            //服务器
            $shift = $shift - self::BITS_SERVER;
            $id |= $this->serverId << $shift;
            echo "serverId=", $this->serverId, "\t";

            //业务
            $shift = $shift - self::BITS_WORKER;
            $id |= $this->workerId << $shift;
            echo "workerId=", $this->workerId, "\t";

            //自增值
            $sequenceNumber = $this->getSequence($id);
            echo "sequenceNumber=", $sequenceNumber, "\t";
            if ($sequenceNumber > pow(2, self::BITS_SEQUENCE)) {
                usleep(1000);
            } else {
                $id |= $sequenceNumber;
                return $id;
            }
        } while (true);
    }

    /**
     * @param $number
     * @return array
     */
    public function reverseNumber($number)
    {
        $uuidItem = [];
        $shift = self::BITS_FULL - self::BITS_PRE - self::BITS_TIME;
        $uuidItem['diffTime'] = ($number >> $shift) & (pow(2, self::BITS_TIME) - 1);

        $shift -= self::BITS_SERVER;
        $uuidItem['serverId'] = ($number >> $shift) & (pow(2, self::BITS_SERVER) - 1);

        $shift -= self::BITS_WORKER;
        $uuidItem['workerId'] = ($number >> $shift) & (pow(2, self::BITS_WORKER) - 1);

        $shift -= self::BITS_SEQUENCE;
        $uuidItem['sequenceNumber'] = ($number >> $shift) & (pow(2, self::BITS_SEQUENCE) - 1);

        $time = (int)($uuidItem['diffTime'] / 1000) + strtotime(self::OFFSET_TIME);
        $uuidItem['generateTime'] = date("Y-m-d H:i:s", $time);

        return $uuidItem;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \ErrorException
     */
    protected function getSequence($id)
    {
        $lua = <<<LUA
            local sequenceKey = KEYS[1]
            local sequenceNumber = redis.call("incr", sequenceKey);
            redis.call("pexpire", sequenceKey, 1);
            return sequenceNumber
LUA;
        $sequence = self::$redis->eval($lua, 1, $id);
        if (isset($luaError)) {
            throw new \ErrorException($luaError);
        } else {
            return $sequence;
        }
    }


    /**
     * @return mixed
     */
    public function getServerId()
    {
        return $this->serverId;
    }

    /**
     * @param $serverId
     * @return $this
     */
    public function setServerId($serverId)
    {
        $this->serverId = $serverId;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getWorkerId()
    {
        return $this->workerId;
    }


    /**
     * @param $workerId
     * @return $this
     */
    public function setWorkerId($workerId)
    {
        $this->workerId = $workerId;
        return $this;
    }
}