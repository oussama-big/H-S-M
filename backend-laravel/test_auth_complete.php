#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\User;
use App\Models\Patient;
use App\Services\PatientService;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║       TEST COMPLET: REGISTRATION → LOGIN (APRÈS CORRECTION)   ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$testCases = [
    [
        'email' => 'alice.test@example.com',
        'password' => 'SecurePass123!',
        'nom' => 'Alice',
        'prenom' => 'Test'
    ],
    [
        'email' => 'bob.test@example.com',
        'password' => 'MyPassword2024',
        'nom' => 'Bob',
        'prenom' => 'Test'
    ]
];

$patientService = app(PatientService::class);
$authService = app(AuthService::class);
$testResults = [];

foreach ($testCases as $index => $testData) {
    $caseNum = $index + 1;
    echo "┌─ TEST CASE #$caseNum ──────────────────────────────────────────┐\n";
    echo "│ Email: {$testData['email']}\n";
    echo "│ Password: {$testData['password']}\n";
    echo "└────────────────────────────────────────────────────────────────┘\n\n";
    
    // Clean up
    User::where('email', $testData['email'])->delete();
    
    $caseResult = [
        'email' => $testData['email'],
        'steps' => []
    ];
    
    // Step 1: Registration
    echo "  [STEP 1] REGISTRATION\n";
    try {
        $patient = $patientService->registerPatient([
            'nom' => $testData['nom'],
            'prenom' => $testData['prenom'],
            'email' => $testData['email'],
            'password' => $testData['password'],
            'date_of_birth' => '1995-05-15',
            'gender' => 'M',
            'blood_type' => 'A+',
        ]);
        
        echo "            ✅ SUCCESS - Patient ID: {$patient->id}, User ID: {$patient->user_id}\n";
        $caseResult['steps'][] = ['name' => 'Registration', 'status' => 'SUCCESS'];
    } catch (\Exception $e) {
        echo "            ❌ FAILED - {$e->getMessage()}\n";
        $caseResult['steps'][] = ['name' => 'Registration', 'status' => 'FAILED'];
        continue;
    }
    
    // Step 2: Verify Database
    echo "  [STEP 2] DATABASE VERIFICATION\n";
    $user = User::where('email', $testData['email'])->first();
    if ($user) {
        $hashCheck = Hash::check($testData['password'], $user->password);
        $hashLength = strlen($user->password);
        $hashPrefix = substr($user->password, 0, 10);
        
        echo "            ✅ User found in DB\n";
        echo "               - Password Hash: $hashPrefix... (length: $hashLength)\n";
        echo "               - Hash::check(): " . ($hashCheck ? "✅ TRUE" : "❌ FALSE") . "\n";
        
        if ($hashCheck && $hashLength == 60 && strpos($user->password, '$2y$') === 0) {
            echo "               - Hash Format: ✅ Valid bcrypt\n";
            $caseResult['steps'][] = ['name' => 'DB Verification', 'status' => 'SUCCESS'];
        } else {
            echo "               - Hash Format: ❌ Invalid\n";
            $caseResult['steps'][] = ['name' => 'DB Verification', 'status' => 'WARNING'];
        }
    } else {
        echo "            ❌ User NOT found in database\n";
        $caseResult['steps'][] = ['name' => 'DB Verification', 'status' => 'FAILED'];
        continue;
    }
    
    // Step 3: Login Test
    echo "  [STEP 3] LOGIN TEST\n";
    $loginUser = $authService->loginUser([
        'email' => $testData['email'],
        'password' => $testData['password']
    ]);
    
    if ($loginUser) {
        echo "            ✅ SUCCESS - Login worked\n";
        echo "               - User ID: {$loginUser->id}\n";
        echo "               - Email: {$loginUser->email}\n";
        echo "               - Role: {$loginUser->role}\n";
        $caseResult['steps'][] = ['name' => 'Login', 'status' => 'SUCCESS'];
    } else {
        echo "            ❌ FAILED - loginUser() returned null\n";
        $caseResult['steps'][] = ['name' => 'Login', 'status' => 'FAILED'];
    }
    
    // Step 4: Token Generation
    echo "  [STEP 4] TOKEN GENERATION\n";
    try {
        $token = $user->createToken('test_token')->plainTextToken;
        echo "            ✅ Token generated successfully\n";
        echo "               - Token: " . substr($token, 0, 20) . "...\n";
        $caseResult['steps'][] = ['name' => 'Token Generation', 'status' => 'SUCCESS'];
    } catch (\Exception $e) {
        echo "            ❌ Token generation failed: {$e->getMessage()}\n";
        $caseResult['steps'][] = ['name' => 'Token Generation', 'status' => 'FAILED'];
    }
    
    $testResults[] = $caseResult;
    echo "\n";
}

// Summary
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                         TEST SUMMARY                           ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$allPassed = true;
foreach ($testResults as $testResult) {
    $passed = true;
    foreach ($testResult['steps'] as $step) {
        if ($step['status'] !== 'SUCCESS') {
            $passed = false;
        }
    }
    
    $status = $passed ? "✅ PASSED" : "⚠️  PARTIAL";
    echo "$status - {$testResult['email']}\n";
    foreach ($testResult['steps'] as $step) {
        $stepStatus = $step['status'] === 'SUCCESS' ? "✅" : ($step['status'] === 'WARNING' ? "⚠️ " : "❌");
        echo "         $stepStatus {$step['name']}\n";
    }
    
    if (!$passed) $allPassed = false;
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
if ($allPassed) {
    echo "║  🎉 ALL TESTS PASSED - AUTHENTICATION SYSTEM WORKING CORRECTLY  ║\n";
} else {
    echo "║  ⚠️  SOME TESTS FAILED - PLEASE REVIEW ABOVE                   ║\n";
}
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
?>
