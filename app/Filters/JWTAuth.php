<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class JWTAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['message' => 'Token de autenticação não fornecido.']);
        }

        $token = substr($authHeader, 7);

        try {
            $secret  = env('jwt.secret');
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            // Make user data available to controllers
            $request->setHeader('X-User-Id', (string) $decoded->sub);
            $request->setHeader('X-User-Email', $decoded->email);
            $request->setHeader('X-User-Name', $decoded->name);
        } catch (ExpiredException $e) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['message' => 'Token expirado. Faça login novamente.']);
        } catch (\Exception $e) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['message' => 'Token inválido.']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after
    }
}
