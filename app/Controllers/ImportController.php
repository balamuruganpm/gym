<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Member;
use App\Services\Database;
use App\Services\Session;
use App\Services\TenantContext;

class ImportController extends Controller
{
    public function index(): string
    {
        $userRole = (string) (Session::get('user.role') ?? Session::get('user_role') ?? '');
        if ($userRole !== 'owner') {
            $this->redirect('dashboard', ['error' => 'Only the gym owner can access member import tools.']);
        }

        return $this->view('imports.index', [
            'pageTitle' => 'Batch Member Import — GymFlow',
        ]);
    }

    /**
     * Download clean starter CSV template.
     */
    public function template(): void
    {
        $headers = [
            'First Name',
            'Last Name',
            'Phone',
            'Email',
            'Gender',
            'Date of Birth (YYYY-MM-DD)',
            'Address',
            'Emergency Contact Name',
            'Emergency Contact Phone',
            'Notes'
        ];

        $sampleRows = [
            [
                'Aman',
                'Gupta',
                '9876543201',
                'aman.gupta@example.com',
                'male',
                '1996-08-14',
                'B-402 Shanti Heights, Mumbai',
                'Sanjay Gupta',
                '9876543299',
                'Focus on strength training & conditioning'
            ],
            [
                'Meera',
                'Nair',
                '9876543202',
                'meera.nair@example.com',
                'female',
                '1999-03-22',
                '12 Palm Avenue, Pune',
                'Anita Nair',
                '9876543298',
                'Zumba and cardio preferences'
            ]
        ];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="gymflow_member_import_template.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        foreach ($sampleRows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    /**
     * Process CSV upload and batch-insert valid members.
     */
    public function upload(): void
    {
        $userRole = (string) (Session::get('user.role') ?? Session::get('user_role') ?? '');
        if ($userRole !== 'owner') {
            $this->redirect('dashboard', ['error' => 'Only the gym owner can upload member files.']);
        }

        $gymId = TenantContext::getGymId();

        if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->back(['error' => 'Please select a valid CSV file to upload.']);
        }

        $tmpPath = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($tmpPath, 'r');
        if (!$handle) {
            $this->back(['error' => 'Could not open uploaded file.']);
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 3) {
            fclose($handle);
            $this->back(['error' => 'Invalid CSV format. Missing required columns.']);
        }

        $insertedCount = 0;
        $skippedCount = 0;
        $errors = [];

        try {
            Database::transaction(function (\PDO $pdo) use ($handle, $gymId, &$insertedCount, &$skippedCount, &$errors) {
                $rowNumber = 1;
                $stmt = $pdo->prepare("
                    INSERT INTO `members` (
                        `gym_id`, `member_number`, `first_name`, `last_name`, 
                        `phone`, `email`, `gender`, `date_of_birth`, 
                        `address`, `emergency_contact_name`, `emergency_contact_phone`, 
                        `notes`, `is_archived`, `created_at`
                    ) VALUES (
                        :gym_id, :member_number, :first_name, :last_name, 
                        :phone, :email, :gender, :date_of_birth, 
                        :address, :emergency_contact_name, :emergency_contact_phone, 
                        :notes, 0, NOW()
                    )
                ");

                while (($data = fgetcsv($handle)) !== false) {
                    $rowNumber++;
                    if (empty(array_filter($data))) {
                        continue; // skip completely empty rows
                    }

                    $firstName = trim($data[0] ?? '');
                    $lastName = !empty($data[1]) ? trim($data[1]) : null;
                    $phone = trim($data[2] ?? '');

                    if (empty($firstName) || empty($phone)) {
                        $skippedCount++;
                        $errors[] = "Row {$rowNumber}: First name and phone are required.";
                        continue;
                    }

                    $email = !empty($data[3]) ? strtolower(trim($data[3])) : null;
                    $gender = !empty($data[4]) ? strtolower(trim($data[4])) : null;
                    if (!in_array($gender, ['male', 'female', 'other'], true)) {
                        $gender = null;
                    }

                    $dob = !empty($data[5]) ? trim($data[5]) : null;
                    if ($dob && !strtotime($dob)) {
                        $dob = null;
                    }

                    $address = !empty($data[6]) ? trim($data[6]) : null;
                    $emName = !empty($data[7]) ? trim($data[7]) : null;
                    $emPhone = !empty($data[8]) ? trim($data[8]) : null;
                    $notes = !empty($data[9]) ? trim($data[9]) : null;

                    $memberNumber = Member::generateMemberNumber($gymId);

                    $stmt->execute([
                        ':gym_id' => $gymId,
                        ':member_number' => $memberNumber,
                        ':first_name' => $firstName,
                        ':last_name' => $lastName,
                        ':phone' => $phone,
                        ':email' => $email,
                        ':gender' => $gender,
                        ':date_of_birth' => $dob,
                        ':address' => $address,
                        ':emergency_contact_name' => $emName,
                        ':emergency_contact_phone' => $emPhone,
                        ':notes' => $notes,
                    ]);

                    $insertedCount++;
                }
            });

            fclose($handle);

            $msg = "Batch import complete! Successfully imported {$insertedCount} members.";
            if ($skippedCount > 0) {
                $msg .= " ({$skippedCount} rows skipped due to missing required fields).";
            }

            $this->redirect('members', ['success' => $msg]);
        } catch (\Throwable $e) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            $this->back(['error' => 'Import error: ' . $e->getMessage()]);
        }
    }
}
