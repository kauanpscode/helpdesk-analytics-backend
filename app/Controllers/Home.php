<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function test()
    {
        return $this->response->setJSON([
            "msg" => "API funcionando"
        ]);
    }
}
