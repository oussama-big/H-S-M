<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Models\Patient;
use App\Services\PatientService;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;

echo "=== TEST COMPLET: REGISTRATION → LOGIN ===\n\n";

// Clean all test users
User::where('email', 'LIKE', '%test%')->delete();

$patientService = app(PatientService::class);
$authService = app(AuthService::class);

$testEmail = 'finaltest' . time() . '@example.com';
$testPassword = 'SecurePassword123!';

echo "📝 Données de test:\n";
echo "   - Email: $testEmail\n";
echo "   - Password: $testPassword\n\n";

// ===== STEP 1: REGISTRATION =====
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✏️  STEP 1: REGISTRATION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    $patient = $patientService->registerPatient([
        'nom' => 'TestFinal',
        'prenom' => 'Complete',
        'email' => $testEmail,
        'password' => $testPassword,
        'date_of_birth' => '1995-01-01',
        'gender' => 'M'
    ]);
    
    echo "✅ REGISTRATION SUCCESSFUL\n\n";
    echo "   Patient ID: {$patient->id}\n";
    echo "   User ID: {$patient->user_id}\n";
    echo "   Email: {$patient->user->email}\n";
    echo "   Num Dossier: {$patient->numDossier}\n\n";
} catch (\Exception $e) {
    echo "❌ REGISTRATION FAILED\n";
    echo "   Error: {$e->getMessage()}\n";
    exit(1);
}

// ===== STEP 2: DATABASE VERIFICATION =====
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔍 STEP 2: DATABASE VERIFICATION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$user = $patient->user;
$hashLength = strlen($user->password);
$hashPrefix = substr($user->password, 0, 15);
$hashCheck = Hash::check($testPassword, $user->password);

echo "   Password Hash (first 15 chars): $hashPrefix...\n";
echo "   Hash Length: $hashLength (expected: 60)\n";
echo "   Hash Algorithm: bcrypt ($2y$12$)\n";
echo "   Hash::check(\$plaintext, \$stored): " . ($hashCheck ? "✅ TRUE" : "❌ FALSE") . "\n\n";

if (!$hashCheck || $hashLength !== 60) {
    echo "❌ DATABASE VERIFICATION FAILED\n";
    echo "   Password hash appears to be invalid\n";
    exit(1);
}

// ===== STEP 3: LOGIN TEST =====
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔑 STEP 3: LOGIN TEST\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$loginUser = $authService->loginUser([
    'email' => $testEmail,
    'password' => $testPassword
]);

if (!$loginUser) {
    echo "❌ LOGIN FAILED\n";
    echo "   AuthService::loginUser() returned null\n";
    exit(1);
}

echo "✅ LOGIN SUCCESSFUL\n\n";
echo "   User ID: {$loginUser->id}\n";
echo "   Email: {$loginUser->email}\n";
echo "   Nom: {$loginUser->nom}\n";
echo "   Prenom: {$loginUser->prenom}\n";
echo "   Role: {$loginUser->role}\n\n";

// ===== STEP 4: TOKEN GENERATION =====
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🎫 STEP 4: TOKEN GENERATION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    $token = $loginUser->createToken('auth_token')->plainTextToken;
    
    echo "✅ TOKEN GENERATED SUCCESSFULLY\n\n";
    echo "   Token Type: Bearer Token (Sanctum)\n";
    echo "   Token (first 30 chars): " . substr($token, 0, 30) . "...\n";
    echo "   Token Length: " . strlen($token) . " characters\n\n";
} catch (\Exception $e) {
    echo "❌ TOKEN GENERATION FAILED\n";
    echo "   Error: {$e->getMessage()}\n";
    exit(1);
}

// ===== FINAL SUMMARY =====
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    🎉 ALL TESTS PASSED! 🎉                     ║\n";
echo "║                                                                ║\n";
echo "║  ✅ Registration successful                                    ║\n";
echo "║  ✅ Password hash stored correctly (single bcrypt)            ║\n";
echo "║  ✅ Hash::check() validation working                          ║\n";
echo "║  ✅ Login successful                                          ║\n";
echo "║  ✅ Token generation working                                  ║\n";
echo "║                                                                ║\n";
echo "║  The authentication system is now fully functional!           ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
?>
