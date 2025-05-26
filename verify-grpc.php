<?php
echo "Verifying gRPC extension installation:\n\n";

if (extension_loaded('grpc')) {
    echo "✅ gRPC extension is successfully installed and loaded\n";
    echo "Version: " . phpversion('grpc') . "\n";
} else {
    echo "❌ gRPC extension is not loaded\n";
    echo "Please check:\n";
    echo "1. php_grpc.dll exists in " . ini_get('extension_dir') . "\n";
    echo "2. extension=grpc is uncommented in " . php_ini_loaded_file() . "\n";
    echo "3. Apache/XAMPP has been restarted\n";
}

// Check all loaded extensions
echo "\nAll loaded extensions:\n";
foreach (get_loaded_extensions() as $ext) {
    echo "- $ext " . phpversion($ext) . "\n";
}
