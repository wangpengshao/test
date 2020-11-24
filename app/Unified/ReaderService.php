<?php

namespace App\Unified;

use App\Models\Wxuser;
use Illuminate\Support\Arr;
use Matrix\Exception;

/**
 * Class ReaderService
 * @package App\Unified
 */
class ReaderService
{
    /**
     * 适配器
     * @var
     */
    private $adapter;

    /**
     * 验证方式
     * @var int
     */
    private $authType = 0;

    /**
     * ReaderService constructor.
     * @param Wxuser $wxuser
     * @param int    $authType 验证方式
     * @param array  $config 自定义配置
     * @throws Exception
     */
    public function __construct(Wxuser $wxuser, int $authType = 1, array $config = [])
    {
        $this->init($wxuser, $authType, $config);
    }

    /**
     * 适配器对应类
     * @return bool|string
     */
    private function getMappingClass()
    {
        switch ($this->authType) {
            case 1:
                return OpenlibAdapter::class;
            case 2:
                return OpacAdapter::class;
            case 3:
                return UamAdapter::class;
            default:
                return false;
        }
    }

    /**
     * @param $wxuser
     * @param $authType
     * @param $config
     * @throws Exception
     */
    private function init($wxuser, $authType, $config)
    {
        $this->authType = ($authType > 1) ? $authType : $wxuser['auth_type'];

        $class = $this->getMappingClass();
        if (!$class) {
            throw new Exception('the auth_type is invalid');
        }
        $adapter = new $class();

        if (empty($config)) {
            $config = Arr::only($wxuser->toArray(), $adapter->mustParams());
        }
        $adapter->setConfig($config);
        $this->setAdapter($adapter);
    }

    /**
     * @param ReaderAdapter $readerAdapter
     */
    private function setAdapter(ReaderAdapter $readerAdapter)
    {
        $this->adapter = $readerAdapter;
    }


    /**
     * 用户认证
     * @param       $rdid
     * @param       $password
     * @param array $other
     * @return mixed
     */
    public function certification(string $rdid, string $password, array $other = [])
    {
        $params = [
            'rdid' => $rdid,
            'password' => $password,
        ];
        $params = array_merge($params, $other);

        return $this->adapter->certification($params);
    }

    /**
     * 检索用户
     * @param string $rdid
     * @param string $IDCard
     * @param array  $other
     * @return mixed
     */
    public function searchUser(string $rdid = '', string $IDCard = '', array $other = [])
    {
        $other['rdid'] = $rdid;
        $other['IDCard'] = $IDCard;
        return $this->adapter->searchUser($other);
    }

    /**
     * 当前借阅
     * @param string $rdid
     * @param array  $other
     * @return mixed
     */
    public function getCurrentLoan(string $rdid, array $other = [])
    {
        $other['rdid'] = $rdid;
        return $this->adapter->currentLoan($other);
    }

    /**
     * 历史借阅
     * @param string $rdid
     * @param array  $other
     * @return mixed
     */
    public function getHistoryLoan(string $rdid, array $other = [])
    {
        $other['rdid'] = $rdid;
        return $this->adapter->historyLoan($other);
    }

    /**
     * 续借操作
     * @param string $rdid
     * @param string $barcode
     * @return mixed
     */
    public function reNewBook(string $rdid, string $barcode)
    {
        return $this->adapter->renewbook($rdid, $barcode);
    }
}
