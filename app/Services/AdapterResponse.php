<?php

namespace App\Services;

trait AdapterResponse
{

    /**
     * @param $message
     * @param bool $status
     * @return array
     */
    private function message($message, $status = true)
    {
        return [
            'message' => $message,
            'status' => $status
        ];
    }

    /**
     * @param $data
     * @param bool $status
     * @return array
     */
    private function success($data, $status = true)
    {
        return [
            'data' => $data,
            'status' => $status
        ];
    }
}
