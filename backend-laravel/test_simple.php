<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Services\PatientService;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;

// Clean
User::where('email', 'like', '%.test%')->delete();

echo "=== TEST DE L'AUTHENTIFICATION APRÈS CORRECTION ===\n\n";

$patientService = app(PatientService::class);
$authService = app(AuthService::class);

echo "1️⃣ ENREGISTREMENT:\n";
$patient = $patientService->registerPatient([
    'nom' => 'TestComplete',
    'prenom' => 'Flow',
    'email' => 'testcomplete@example.com',
    'password' => 'MyTestPassword123',
    'date_of_birth' => '1995-01-01',
    'gender' => 'M'
]);

echo "   ✅ Patient enregistré\n";
echo "   - Patient ID: {$patient->id}\n";
echo "   - Email: {$patient->user->email}\n\n";

echo "2️⃣ VÉRIFICATION BASE DE DONNÉES:\n";
$user = $patient->user;
$hashOK = Hash::check('MyTestPassword123', $user->password);
echo "   - Password hash: " . substr($user->password, 0, 25) . "...\n";
echo "   - Hash length: " . strlen($user->password) . " (correct: 60)\n";
echo "   - Hash::check(): " . ($hashOK ? "✅ TRUE" : "❌ FALSE") . "\n\n";

echo "3️⃣ TEST DE CONNEXION:\n";
$loginResult = $authService->loginUser([
    'email' => 'testcomplete@example.com',
    'password' => 'MyTestPassword123'
]);

if ($loginResult) {
    echo "   ✅ CONNEXION RÉUSSIE!\n";
    echo "   - User ID: {$loginResult->id}\n";
    echo "   - Email: {$loginResult->email}\n";
    echo "   - Role: {$loginResult->role}\n\n";
    
    echo "4️⃣ GÉNÉRATION TOKEN:\n";
    $token = $loginResult->createToken('test_token')->plainTextToken;
    echo "   ✅ Token généré\n";
    echo "   - Token (first 30 chars): " . substr($token, 0, 30) . "...\n\n";
    
    echo "╔════════════════════════════════════════════════════════╗\n";
    echo "║ 🎉 TEST COMPLET RÉUSSI - SYSTÈME D'AUTH FONCTIONNE!   ║\n";
    echo "╚════════════════════════════════════════════════════════╝\n";
} else {
    echo "   ❌ CONNEXION ÉCHOUÉE - loginUser() returned null\n";
}
?>
