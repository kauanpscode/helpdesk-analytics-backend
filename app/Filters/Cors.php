<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Cors implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (strtolower($request->getMethod()) === 'options') {
            return service('response')
                ->setHeader('Access-Control-Allow-Origin', '*')
                ->setHeader(
                    'Access-Control-Allow-Headers',
                    'Origin, X-Requested-With, Content-Type, Accept, Authorization'
                )
                ->setHeader(
                    'Access-Control-Allow-Methods',
                    'GET, POST, OPTIONS, PUT, DELETE'
                )
                ->setStatusCode(200);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader(
                'Access-Control-Allow-Headers',
                'Origin, X-Requested-With, Content-Type, Accept, Authorization'
            )
            ->setHeader(
                'Access-Control-Allow-Methods',
                'GET, POST, OPTIONS, PUT, DELETE'
            );
    }
}