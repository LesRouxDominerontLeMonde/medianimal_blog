<?php
/**
 * Script pour vérifier les prérequis PHP du projet
 * À exécuter avant déploiement
 */

echo "🔍 Vérification des prérequis PHP...\n\n";

$required_extensions = [
    'intl' => 'Extension intl (nécessaire pour EasyAdmin date/time)',
    'pdo' => 'Extension PDO (base de données)',
    'json' => 'Extension JSON',
    'mbstring' => 'Extension mbstring (gestion UTF-8)',
    'tokenizer' => 'Extension tokenizer (Symfony)',
    'xml' => 'Extension XML',
    'ctype' => 'Extension ctype',
    'iconv' => 'Extension iconv',
];

$missing = [];
$found = [];

foreach ($required_extensions as $ext => $description) {
    if (extension_loaded($ext)) {
        echo "✅ $ext : $description\n";
        $found[] = $ext;
    } else {
        echo "❌ $ext : $description\n";
        $missing[] = $ext;
    }
}

echo "\n📊 Résumé :\n";
echo "✅ Extensions trouvées : " . count($found) . "\n";
echo "❌ Extensions manquantes : " . count($missing) . "\n";

if (!empty($missing)) {
    echo "\n🚨 ATTENTION : Extensions manquantes !\n";
    echo "Installez ces extensions avant de déployer :\n";
    foreach ($missing as $ext) {
        echo "  - $ext\n";
    }
    exit(1);
} else {
    echo "\n🎉 Tous les prérequis sont satisfaits !\n";
    exit(0);
} 