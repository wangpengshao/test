<?php

namespace App\Services;

/**
 * Class RandTrianglePackage
 * @package App\Services\RedPaper
 */
class RandRedPackage
{
    //总额
    /**
     * @var
     */
    public $totalMoney;

    //红包数量
    /**
     * @var
     */
    public $num;

    //随机红包最小值
    /**
     * @var
     */
    public $minMoney;

    //随机红包最大值
    /**
     * @var
     */
    public $maxMoney;

    //修数据方式：NO_LEFT: 红包总额 = 预算总额；CAN_LEFT: 红包总额 <= 预算总额
    /**
     * @var
     */
    public $formatType;

    //预算剩余金额
    /**
     * @var
     */
    public $leftMoney;

    /**
     * @param $totalMoney
     * @param $num
     * @param $rangeStart
     * @param $rangEnd
     * @param string $randFormatType
     * @return RandRedPackage
     */
    public static function setOptions($totalMoney, $num, $rangeStart, $rangEnd, $randFormatType = 'No_Left')
    {
        $self = new self();
        $self->totalMoney = $totalMoney;
        $self->num = $num;
        $self->minMoney = $rangeStart;
        $self->maxMoney = $rangEnd;
        $self->leftMoney = $totalMoney;
        return $self;
    }

    /**
     * @return array|mixed
     */
    public function create()
    {
        $data = array();
        if (false == $this->isCanBuilder()) {
            return $data;
        }
        $leftMoney = $this->leftMoney;
        for ($i = 1; $i <= $this->num; $i++) {
            $data[$i] = $this->fx($i);
            $leftMoney = $leftMoney - $data[$i];
        }
        //修数据
        list($okLeftMoney, $okData) = $this->format($leftMoney, $data);
        //随机排序
        shuffle($okData);
        $this->leftMoney = $okLeftMoney;
        return $okData;
    }

    /**
     * 是否能够发随机红包
     *
     * @access public
     */
    public function isCanBuilder()
    {
        if (false == is_int($this->num) || $this->num <= 0) {
            return false;
        }
        if (false == is_numeric($this->totalMoney) || $this->totalMoney <= 0) {
            return false;
        }
        //均值
        $avgMoney = $this->totalMoney / 1.0 / $this->num;
        //均值小于最小值
        if ($avgMoney < $this->minMoney) {
            return false;
        }
        return true;
    }

    /**
     * 获取剩余金额
     *
     * @access public
     * @return void
     */
    public function getLeftMoney()
    {
        return $this->leftMoney;
    }

    /**
     * 随机红包生成函数。三角函数。[(1,0.01),($num/2,$avgMoney),($num,0.01)]
     *
     * @param mixed $x ,1 <= $x <= $this->num;
     * @access public
     * @return void
     */
    public function fx($x)
    {
        if (false == $this->isCanBuilder()) {
            return 0;
        }
        if ($x < 1 || $x > $this->num) {
            return 0;
        }
        $x1 = 1;
        $y1 = $this->minMoney;
        //我的峰值
        $y2 = $this->maxMoney;
        //中间点
        $x2 = ceil($this->num / 1.0 / 2);
        //最后点
        $x3 = $this->num;
        $y3 = $this->minMoney;
        //当x1,x2,x3都是1的时候(竖线)
        if ($x1 == $x2 && $x2 == $x3) {
            return $y2;
        }
        // '/_\'三角形状的线性方程
        //'/'部分
        if ($x1 != $x2 && $x >= $x1 && $x <= $x2) {

            $y = 1.0 * ($x - $x1) / ($x2 - $x1) * ($y2 - $y1) + $y1;
            return number_format($y, 2, '.', '');
        }
        //'\'形状
        if ($x2 != $x3 && $x >= $x2 && $x <= $x3) {
            $y = 1.0 * ($x - $x2) / ($x3 - $x2) * ($y3 - $y2) + $y2;
            return number_format($y, 2, '.', '');
        }
        return 0;
    }
    /**
     *  格式化修红包数据
     * @param $leftMoney
     * @param array $data
     * @return array
     */
    private function format($leftMoney, array $data)
    {
        //不能发随机红包
        if (false == $this->isCanBuilder()) {
            return array($leftMoney, $data);
        }
        //红包剩余是0
        if (0 == $leftMoney) {
            return array($leftMoney, $data);
        }
        //数组为空
        if (count($data) < 1) {
            return array($leftMoney, $data);
        }
        //如果是可以有剩余，并且$leftMoney > 0
        if ('Can_Left' == $this->formatType
            && $leftMoney > 0) {
            return array($leftMoney, $data);
        }
        //我的峰值
        $myMax = $this->maxMoney;
        // 如果还有余钱，则尝试加到小红包里，如果加不进去，则尝试下一个。
        while ($leftMoney > 0) {
            $found = 0;
            foreach ($data as $key => $val) {
                //减少循环优化
                if ($leftMoney <= 0) {
                    break;
                }
                //预判
                $afterLeftMoney = (double)$leftMoney - 0.01;
                $afterVal = (double)$val + 0.01;
                if ($afterLeftMoney >= 0 && $afterVal <= $myMax) {
                    $found = 1;
                    $data[$key] = number_format($afterVal, 2, '.', '');
                    $leftMoney = $afterLeftMoney;
                    //精度
                    $leftMoney = number_format($leftMoney, 2, '.', '');
                }
            }
            //如果没有可以加的红包，需要结束,否则死循环
            if ($found == 0) {
                break;
            }
        }
        //如果$leftMoney < 0 ,说明生成的红包超过预算了，需要减少部分红包金额
        while ($leftMoney < 0) {
            $found = 0;
            foreach ($data as $key => $val) {
                if ($leftMoney >= 0) {
                    break;
                }
                //预判
                $afterLeftMoney = (double)$leftMoney + 0.01;
                $afterVal = (double)$val - 0.01;
                if ($afterLeftMoney <= 0 && $afterVal >= $this->minMoney) {
                    $found = 1;
                    $data[$key] = number_format($afterVal, 2, '.', '');
                    $leftMoney = $afterLeftMoney;
                    $leftMoney = number_format($leftMoney, 2, '.', '');
                }
            }
            //如果一个减少的红包都没有的话，需要结束，否则死循环
            if ($found == 0) {
                break;
            }
        }
        return array($leftMoney, $data);
    }

}

