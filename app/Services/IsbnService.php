<?php

namespace App\Services;

class IsbnService
{
    /**
     * @param $isbn
     *
     * @return int
     */
    function is_isbn($isbn)
    {
        $len = strlen($isbn);
        if ($len != 10 && $len != 13)
            return false;
        $rc = $this->isbn_compute($isbn, $len);
        if ($isbn[$len - 1] != $rc)   /* ISBN尾数与计算出来的校验码不符 */
            return false;
        else
            return true;
    }

    /**
     * @param $isbn
     * @param $len
     *
     * @return float|int
     */
    function isbn_sum($isbn, $len)
    {
        $sum = 0;
        if ($len == 10) {
            for ($i = 0; $i < $len - 1; $i++) {
                $sum = $sum + (int)$isbn[$i] * ($len - $i);
            }
        } elseif ($len == 13) {
            for ($i = 0; $i < $len - 1; $i++) {
                if ($i % 2 == 0)
                    $sum = $sum + (int)$isbn[$i];
                else
                    $sum = $sum + (int)$isbn[$i] * 3;
            }
        }
        return $sum;
    }

    /**
     * @param $isbn
     * @param $len
     *
     * @return string
     */
    function isbn_compute($isbn, $len)
    {
        $rc = '';
        if ($len == 10) {
            $digit = 11 - $this->isbn_sum($isbn, $len) % 11;
            if ($digit == 10)
                $rc = 'X';
            else if ($digit == 11)
                $rc = '0';
            else
                $rc = (string)$digit;
        } else if ($len == 13) {
            $digit = 10 - $this->isbn_sum($isbn, $len) % 10;
            if ($digit == 10)
                $rc = '0';
            else
                $rc = (string)$digit;
        }
        return $rc;
    }
}
