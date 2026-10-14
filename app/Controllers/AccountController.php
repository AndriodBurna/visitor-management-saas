<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\UserModel;

class AccountController extends BaseController
{
    public function profile()
    {
        $user = (new UserModel())->find((int) session('user_id'));

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Your account could not be found.');
        }

        return view('account/profile', [
            'title' => 'My Profile',
            'activeMenu' => 'account-profile',
            'user' => $user,
        ]);
    }

    public function updateProfile()
    {
        $userModel = new UserModel();
        $userId = (int) session('user_id');
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Your account could not be found.');
        }

        $name = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));
        $phone = trim((string) $this->request->getPost('phone'));
        $avatar = $this->request->getFile('avatar');

        if ($name === '' || strlen($name) < 2) {
            return redirect()->back()->withInput()->with('errors', ['name' => 'Enter a name with at least 2 characters.']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('errors', ['email' => 'Enter a valid email address.']);
        }

        $duplicate = $userModel->where('email', $email)->where('id !=', $userId)->first();
        if ($duplicate) {
            return redirect()->back()->withInput()->with('errors', ['email' => 'This email address is already in use.']);
        }

        $data = ['name' => $name, 'email' => $email, 'phone' => $phone];
        if ($avatar && $avatar->getError() !== UPLOAD_ERR_NO_FILE) {
            if (!$avatar->isValid() || !in_array($avatar->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true)) {
                return redirect()->back()->withInput()->with('errors', ['avatar' => 'Upload a valid JPG, PNG, or WEBP image.']);
            }
            if ($avatar->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()->withInput()->with('errors', ['avatar' => 'The profile image must be 2 MB or smaller.']);
            }

            $avatarDirectory = FCPATH . 'uploads/avatars';
            if (!is_dir($avatarDirectory) && !mkdir($avatarDirectory, 0755, true) && !is_dir($avatarDirectory)) {
                return redirect()->back()->withInput()->with('errors', ['avatar' => 'The image upload directory is not writable.']);
            }

            $avatarName = $avatar->getRandomName();
            $avatar->move($avatarDirectory, $avatarName);
            $data['avatar'] = 'uploads/avatars/' . $avatarName;
        }

        if (!$userModel->update($userId, $data)) {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }

        session()->set('user_name', $name);
        if (!empty($data['avatar'])) {
            session()->set('user_avatar', $data['avatar']);
            if (!empty($user['avatar']) && str_starts_with($user['avatar'], 'uploads/avatars/')) {
                $oldAvatar = FCPATH . $user['avatar'];
                if (is_file($oldAvatar)) {
                    unlink($oldAvatar);
                }
            }
        }
        (new AuditLogModel())->record('update', 'user', 'Account profile updated.', $userId, $user['organization_id'] ? (int) $user['organization_id'] : null, $user['branch_id'] ? (int) $user['branch_id'] : null);

        return redirect()->to('/account/profile')->with('success', 'Your profile was updated successfully.');
    }

    public function password()
    {
        $securityActivity = (new AuditLogModel())
            ->where('actor_id', (int) session('user_id'))
            ->whereIn('action', ['login', 'logout', 'update'])
            ->orderBy('created_at', 'DESC')
            ->limit(6)
            ->find();

        return view('account/password', [
            'title' => 'Security Settings',
            'activeMenu' => 'account-password',
            'securityActivity' => $securityActivity,
        ]);
    }

    public function updatePassword()
    {
        $userModel = new UserModel();
        $userId = (int) session('user_id');
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Your account could not be found.');
        }

        $currentPassword = (string) $this->request->getPost('current_password');
        $newPassword = (string) $this->request->getPost('new_password');
        $confirmation = (string) $this->request->getPost('new_password_confirmation');

        if (!password_verify($currentPassword, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('errors', ['current_password' => 'The current password is incorrect.']);
        }
        if (strlen($newPassword) < 8) {
            return redirect()->back()->withInput()->with('errors', ['new_password' => 'Your new password must be at least 8 characters.']);
        }
        if ($newPassword !== $confirmation) {
            return redirect()->back()->withInput()->with('errors', ['new_password_confirmation' => 'The password confirmation does not match.']);
        }
        if (password_verify($newPassword, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('errors', ['new_password' => 'Choose a password different from your current one.']);
        }

        $userModel->update($userId, ['password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)]);
        (new AuditLogModel())->record('update', 'user', 'Account password changed.', $userId, $user['organization_id'] ? (int) $user['organization_id'] : null, $user['branch_id'] ? (int) $user['branch_id'] : null);

        return redirect()->to('/account/password')->with('success', 'Your password was changed successfully.');
    }
}
