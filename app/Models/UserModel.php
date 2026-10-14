<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

   protected $allowedFields = [
            'organization_id',
            'branch_id',
            'name',
            'email',
            'phone',
            'avatar',
            'password_hash',
            'role',
            'status',
            'last_login_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    protected $validationRules = [
        'name'  => 'required|min_length[2]|max_length[150]',
        'email' => 'required|valid_email|max_length[150]',
        'role' => 'required|in_list[organization_admin,branch_manager,receptionist]',
        'status' => 'required|in_list[active,inactive,suspended]',
    ];
}