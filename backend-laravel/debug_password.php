<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Get first user
$user = User::first();

if ($user) {
    echo "=== USER DATABASE INFO ===\n";
    echo "ID: " . $user->id . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Password stored (first 50 chars): " . substr($user->password, 0, 50) . "...\n";
    echo "Password length: " . strlen($user->password) . "\n";
    echo "\n";
    
    echo "=== PASSWORD VERIFICATION TEST ===\n";
    $testPassword = 'password123';
    $result = Hash::check($testPassword, $user->password);
    echo "Hash::check('password123', stored_password): " . ($result ? 'TRUE ✓' : 'FALSE ✗') . "\n";
    echo "\n";
    
    echo "=== HASH INFO ===\n";
    $info = Hash::info($user->password);
    echo "Algorithm: " . $info['alg'] . "\n";
    echo "Hash info: " . json_encode($info) . "\n";
} else {
    echo "No users found in database\n";
}
?>
