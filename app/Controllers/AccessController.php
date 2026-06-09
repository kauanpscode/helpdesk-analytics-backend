<?php

namespace App\Controllers;

use App\Models\AcessModel;
use Firebase\JWT\JWT;

use Google\Client;

class AccessController extends BaseController
{
    /**
     * POST /api/login
     * Expects JSON: { email, password }
     * Returns JSON: { token, user }
     */

    public function __construct()
    {
        $this->model = new AcessModel();
    }
    public function login()
    {
        $json = $this->request->getJSON(true);

        $email    = $json['email']    ?? '';
        $password = $json['password'] ?? '';

        // Validate input
        if (empty($email) || empty($password)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['message' => 'E-mail e senha são obrigatórios.']);
        }

        // Find user by email
        $user = $this->model->getByEmail($email);

        if (!$user) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['message' => 'E-mail ou senha inválidos.']);
        }

        // Verify the password
        if (!password_verify($password, $user['password'])) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['message' => 'E-mail ou senha inválidos.']);
        }

        // Generate JWT token
        $secret = env('jwt.secret');
        $issuedAt = time();
        $expiration = $issuedAt + (60 * 60 * 24); // 24 hours

        $payload = [
            'iat'   => $issuedAt,
            'exp'   => $expiration,
            'sub'   => $user['id'],
            'email' => $user['email'],
            'name'  => $user['name'],
        ];

        $token = JWT::encode($payload, $secret, 'HS256');

        return $this->response->setJSON([
            'token' => $token,
            'user'  => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
            ],
        ]);
    }

    /**
     * POST /api/register
     * Expects JSON: { name, email, password }
     * Returns JSON: { message, user }
     */
    public function register()
    {
        $json = $this->request->getJSON(true);

        $name     = $json['name']     ?? '';
        $email    = $json['email']    ?? '';
        $password = $json['password'] ?? '';

        // Validate input
        if (empty($name) || empty($email) || empty($password)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['message' => 'Nome, e-mail e senha são obrigatórios.']);
        }

        if (strlen($password) < 6) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['message' => 'A senha deve ter no mínimo 6 caracteres.']);
        }

        // Check if email already exists
        if ($this->model->getByEmail($email)) {
            return $this->response
                ->setStatusCode(409)
                ->setJSON(['message' => 'Este e-mail já está cadastrado.']);
        }

        // Create user with hashed password
        $userId = $this->model->insert([
            'name'     => $name,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
        ]);

        if (!$userId) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['message' => 'Erro ao criar conta. Tente novamente.']);
        }

        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'message' => 'Conta criada com sucesso!',
                'user'    => [
                    'id'    => $userId,
                    'name'  => $name,
                    'email' => $email,
                ],
            ]);
    }
    /**
     * GET /api/me (Protected)
     * Returns the authenticated user's data
     */
    public function profile()
    {
        $userId = $this->request->getHeaderLine('X-User-Id');

        $AcessModel = new AcessModel();
        $user = $AcessModel->find($userId);

        if (!$user) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['message' => 'Usuário não encontrado.']);
        }

        return $this->response->setJSON([
            'user' => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
            ],
        ]);
    }

    public function googleLogin()
    {
        $json = $this->request->getJson(true);

        $googleToken = $json['token'] ?? null;

        if (!$googleToken) {
            return $this->response->setStatusCode(400)->setJSON([
                'message' => 'Token Google não informado.'
            ]);
        }

        $client = new Client([
            'client_id' => env('google.clientId')
        ]);

        $payload = $client->verifyIdToken($googleToken);

        if (!$payload) {
            return $this->response->setStatusCode(400)->setJSON([
                'message' => 'Token Google inválido.'
            ]);
        }

        $email = $payload['email'];
        $name = $payload['name'] ?? 'Usuário Google';

        $user = $this->model->getByEmail($email);

        if (!$user) {
            $userId = $this->model->insert([
                'name'     => $name,
                'email'    => $email,
                'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
            ]);

            $user = $this->model->find($userId);
        }

        $secret = env('jwt.secret');

        $issuedAt = time();
        $expiration = $issuedAt + (60 * 60 * 24);

        $jwtPayload = [
            'iat' => $issuedAt,
            'exp' => $expiration,
            'sub' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name']
        ];

        $token = JWT::encode($jwtPayload, $secret, 'HS256');

        return $this->response->setJSON([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
            ]
        ]);
    }
}
