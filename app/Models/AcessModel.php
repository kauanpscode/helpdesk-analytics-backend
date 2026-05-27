<?php

namespace App\Models;

use CodeIgniter\Model;

class AcessModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = ['name', 'email', 'password'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $returnType = 'array';

    public function getByEmail($email)
    {
        $sql = "SELECT * FROM users
                WHERE email = ?";

        return $this->query($sql, [$email])->getRowArray();
    }
}
