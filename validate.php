<?php
/**
 * Package Validation Script
 * Run this to validate the package before publishing to Packagist
 */

$baseDir = __DIR__;
$errors = [];
$warnings = [];

echo "🔍 Validating Swissup Theme Store Views package...\n\n";

// Check required files
$requiredFiles = [
    'composer.json',
    'README.md',
    'LICENSE',
    'registration.php',
    'etc/module.xml',
    'etc/di.xml',
    'Console/Command/CreateThemeStoreViews.php'
];

echo "📁 Checking required files...\n";
foreach ($requiredFiles as $file) {
    if (file_exists($baseDir . '/' . $file)) {
        echo "  ✅ $file\n";
    } else {
        $errors[] = "Missing required file: $file";
        echo "  ❌ $file\n";
    }
}

// Validate composer.json
echo "\n📦 Validating composer.json...\n";
$composerFile = $baseDir . '/composer.json';
if (file_exists($composerFile)) {
    $composer = json_decode(file_get_contents($composerFile), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errors[] = "Invalid JSON in composer.json";
        echo "  ❌ Invalid JSON syntax\n";
    } else {
        // Check required fields
        $requiredFields = ['name', 'description', 'type', 'license', 'require'];
        foreach ($requiredFields as $field) {
            if (isset($composer[$field])) {
                echo "  ✅ $field\n";
            } else {
                $errors[] = "Missing required field in composer.json: $field";
                echo "  ❌ $field\n";
            }
        }
        
        // Check if name follows convention
        if (isset($composer['name']) && !preg_match('/^swissup\/[a-z0-9-]+$/', $composer['name'])) {
            $warnings[] = "Package name should follow swissup/package-name convention";
            echo "  ⚠️  Package name format\n";
        }
        
        // Check if type is magento2-module
        if (isset($composer['type']) && $composer['type'] !== 'magento2-module') {
            $errors[] = "Type should be 'magento2-module'";
            echo "  ❌ Type should be 'magento2-module'\n";
        } else {
            echo "  ✅ Type is magento2-module\n";
        }
    }
} else {
    $errors[] = "composer.json not found";
}

// Check GitHub URLs
echo "\n🔗 Checking GitHub repository URLs...\n";
if (isset($composer['homepage'])) {
    if (strpos($composer['homepage'], 'github.com/swissup/theme-storeviews') !== false) {
        echo "  ✅ Homepage URL\n";
    } else {
        $warnings[] = "Homepage URL should point to GitHub repository";
        echo "  ⚠️  Homepage URL\n";
    }
}

// Check module.xml
echo "\n🔧 Validating module.xml...\n";
$moduleFile = $baseDir . '/etc/module.xml';
if (file_exists($moduleFile)) {
    $moduleXml = simplexml_load_file($moduleFile);
    if ($moduleXml !== false) {
        if (isset($moduleXml->module['name']) && $moduleXml->module['name'] == 'Swissup_ThemeStoreViews') {
            echo "  ✅ Module name\n";
        } else {
            $errors[] = "Module name should be 'Swissup_ThemeStoreViews'";
            echo "  ❌ Module name\n";
        }
    } else {
        $errors[] = "Invalid XML in module.xml";
        echo "  ❌ Invalid XML\n";
    }
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 VALIDATION SUMMARY\n";
echo str_repeat("=", 50) . "\n";

if (empty($errors)) {
    echo "✅ All validation checks passed!\n";
    echo "🚀 Package is ready for publication to Packagist.\n\n";
    
    echo "Next steps:\n";
    echo "1. Push to GitHub: https://github.com/swissup/theme-storeviews\n";
    echo "2. Create release tag: git tag v1.0.0 && git push --tags\n";
    echo "3. Submit to Packagist: https://packagist.org/packages/submit\n";
} else {
    echo "❌ Found " . count($errors) . " error(s) that must be fixed:\n";
    foreach ($errors as $error) {
        echo "  • $error\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️  Found " . count($warnings) . " warning(s):\n";
    foreach ($warnings as $warning) {
        echo "  • $warning\n";
    }
}

echo "\n";
