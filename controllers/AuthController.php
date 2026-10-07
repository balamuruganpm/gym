<?php
/**
 * Auth Controller
 * Handles user login, authentication verification, redirection according to role, and logout.
 */

class AuthController {
    public function showLogin() {
        if (Auth::check()) {
            $this->redirectByRole(Auth::role());
            return;
        }

        View::render('auth/login', [
            'pageTitle' => 'Sign In - FitPulse Gym Platform'
        ], 'layouts/auth');
    }

    public function processLogin() {
        CSRF::validateOrAbort();

        $login = isset($_POST['login']) ? trim($_POST['login']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        $validator = Validator::make([
            'login' => $login,
            'password' => $password
        ], [
            'login' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if (Auth::attempt($login, $password)) {
            $user = Auth::user();
            $displayName = $user['name'] ?? 'User';
            Session::flash('success', "Welcome back, {$displayName}!");
            $this->redirectByRole($user['role']);
            return;
        }

        Session::flash('error', 'Invalid login credentials. Please try again.');
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    public function logout() {
        Auth::logout();
        Session::flash('info', 'You have been successfully logged out.');
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    private function redirectByRole($role) {
        if ($role === 'member') {
            header('Location: ' . BASE_URL . '/member/dashboard');
        } elseif ($role === 'superadmin') {
            header('Location: ' . BASE_URL . '/admin/coder-control');
        } else {
            header('Location: ' . BASE_URL . '/admin/dashboard');
        }
        exit;
    }
}
