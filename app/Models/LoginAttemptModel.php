<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginAttemptModel extends Model
{
    protected $table = 'login_attempts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['email', 'ip_address', 'attempts', 'locked_until', 'updated_at'];
    protected $useTimestamps = false;

    public function isLocked(string $email, string $ip): bool
    {
        $attempt = $this->where(['email' => $email, 'ip_address' => $ip])->first();

        return $attempt && !empty($attempt['locked_until']) && strtotime($attempt['locked_until']) > time();
    }

    public function recordFailure(string $email, string $ip, int $maxAttempts, int $lockoutMinutes): void
    {
        $attempt = $this->where(['email' => $email, 'ip_address' => $ip])->first();
        $count = ((int) ($attempt['attempts'] ?? 0)) + 1;
        $lockedUntil = $count >= $maxAttempts ? date('Y-m-d H:i:s', time() + ($lockoutMinutes * 60)) : null;
        $data = ['email' => $email, 'ip_address' => $ip, 'attempts' => $count, 'locked_until' => $lockedUntil, 'updated_at' => date('Y-m-d H:i:s')];

        if ($attempt) {
            $this->update($attempt['id'], $data);
        } else {
            $this->insert($data);
        }
    }

    public function clear(string $email, string $ip): void
    {
        $this->where(['email' => $email, 'ip_address' => $ip])->delete();
    }
}
