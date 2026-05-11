<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class TestController extends BaseController
{
    use ResponseTrait;

    public function getStatus()
    {
        $data = [
            'status'   => 'Connected',
            'message'  => 'Hello from CodeIgniter 4!',
            'date'     => date('Y-m-d H:i:s'),
            'stack'    => ['PHP 8.x', 'CI4', 'Vue 3', 'Vite']
        ];

        return $this->respond($data, 200);
    }
}