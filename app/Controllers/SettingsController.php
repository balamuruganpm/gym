<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Database;
use App\Services\Session;
use App\Services\TenantContext;
use App\Validators\Validator;

class SettingsController extends Controller
{
    public function index(): string
    {
        $gymId = TenantContext::getGymId();
        $gym = Database::fetchOne("SELECT * FROM `gyms` WHERE `id` = :id LIMIT 1", [':id' => $gymId]);

        return $this->view('settings.index', [
            'pageTitle' => 'Gym Settings & Preferences — GymFlow',
            'gym' => $gym,
        ]);
    }

    public function update(): void
    {
        $gymId = TenantContext::getGymId();
        $userRole = (string) Session::get('user.role');

        if ($userRole !== 'owner') {
            $this->back(['error' => 'Only the gym owner can update gym profile settings.']);
        }

        $validator = Validator::make($_POST, [
            'name' => 'required|min:2|max:150',
            'phone' => 'required|min:8|max:25',
        ]);

        if ($validator->fails()) {
            $this->back(['errors' => $validator->errors()]);
        }

        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $email = !empty($_POST['email']) ? strtolower(trim($_POST['email'])) : null;
        $address = !empty($_POST['address']) ? trim($_POST['address']) : null;
        $currencySymbol = !empty($_POST['currency_symbol']) ? trim($_POST['currency_symbol']) : '₹';
        $currencyCode = !empty($_POST['currency_code']) ? strtoupper(trim($_POST['currency_code'])) : 'INR';
        $timezone = !empty($_POST['timezone']) ? trim($_POST['timezone']) : 'Asia/Kolkata';

        try {
            Database::query("
                UPDATE `gyms` SET 
                    `name` = :name,
                    `phone` = :phone,
                    `email` = :email,
                    `address` = :address,
                    `currency_symbol` = :currency_symbol,
                    `currency_code` = :currency_code,
                    `timezone` = :timezone,
                    `updated_at` = NOW()
                WHERE `id` = :id
            ", [
                ':id' => $gymId,
                ':name' => $name,
                ':phone' => $phone,
                ':email' => $email,
                ':address' => $address,
                ':currency_symbol' => $currencySymbol,
                ':currency_code' => $currencyCode,
                ':timezone' => $timezone,
            ]);

            // Refresh session gym object
            $updatedGym = Database::fetchOne("SELECT * FROM `gyms` WHERE `id` = :id LIMIT 1", [':id' => $gymId]);
            Session::set('gym', $updatedGym);

            $this->redirect('settings', ['success' => 'Gym profile & financial preferences saved successfully!']);
        } catch (\Throwable $e) {
            $this->back(['error' => 'Settings update failed: ' . $e->getMessage()]);
        }
    }
}
