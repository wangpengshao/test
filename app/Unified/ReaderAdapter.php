<?php

namespace App\Unified;


/**
 * Interface ReaderAdapter
 * @package App\Unified
 */
interface ReaderAdapter
{
    public function certification($params);

    public function mustParams(): array;

    public function setConfig($config);

    public function searchUser($params);

    public function currentLoan($params);

    public function historyLoan($params);

    public function renewbook($rdid, $barcode);
}
