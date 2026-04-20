<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Services\PatientService;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;

echo "=== COMPLETE FLOW TEST ===\n\n";

// Test data
$testEmail = 'testflow@example.com';
$testPassword = 'testpass123';

// Clean up old test user if exists
User::where('email', $testEmail)->delete();

echo "1. REGISTERING NEW PATIENT\n";
echo "   Email: $testEmail\n";
echo "   Password: $testPassword\n\n";

try {
    $patientService = app(PatientService::class);
    $patient = $patientService->registerPatient([
        'nom' => 'Test',
        'prenom' => 'Flow',
        'email' => $testEmail,
        'password' => $testPassword,
        'date_of_birth' => '1990-01-01',
        'gender' => 'M',
        'blood_type' => 'O+',
    ]);
    
    echo "   ✓ Patient registered successfully\n";
    echo "   Patient ID: " . $patient->id . "\n";
    echo "   User ID: " . $patient->user->id . "\n\n";
} catch (\Exception $e) {
    echo "   ✗ Registration failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "2. VERIFYING PASSWORD IN DATABASE\n";
$user = User::where('email', $testEmail)->first();
echo "   Email in DB: " . $user->email . "\n";
echo "   Password hash (first 50 chars): " . substr($user->password, 0, 50) . "...\n";
echo "   Hash length: " . strlen($user->password) . "\n";
echo "   Hash::check('$testPassword', hash): " . (Hash::check($testPassword, $user->password) ? 'TRUE ✓' : 'FALSE ✗') . "\n\n";

echo "3. TESTING LOGIN WITH AUTHSERVICE\n";
$authService = app(AuthService::class);
$loginUser = $authService->loginUser([
    'email' => $testEmail,
    'password' => $testPassword
]);

if ($loginUser) {
    echo "   ✓ Login successful!\n";
    echo "   User ID: " . $loginUser->id . "\n";
    echo "   Email: " . $loginUser->email . "\n";
    echo "   Role: " . $loginUser->role . "\n";
} else {
    echo "   ✗ Login failed - loginUser returned null\n";
    echo "   Checking if user exists: " . ($user ? 'YES' : 'NO') . "\n";
    echo "   Direct password check: " . (Hash::check($testPassword, $user->password) ? 'TRUE ✓' : 'FALSE ✗') . "\n";
}

echo "\n=== END OF TEST ===\n";
?>
