<?php
$files = [
    'resources/lang/en/app.php',
    'resources/lang/ar/app.php',
    'resources/lang/fr/app.php',
    'resources/lang/es/app.php'
];
$defaults = [
    'general_information' => 'General Information',
    'site_name' => 'Site Name',
    'site_settings' => 'Site Settings'
];
$arab = [
    'general_information' => 'معلومات عامة',
    'site_name' => 'اسم الموقع',
    'site_settings' => 'إعدادات الموقع'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "Skipping missing file: $file\n";
        continue;
    }
    $arr = include $file;
    foreach ($defaults as $k => $v) {
        if (!array_key_exists($k, $arr)) {
            if (strpos($file, '/ar/') !== false) $arr[$k] = $arab[$k];
            else $arr[$k] = $v;
            echo "Added $k to $file\n";
        }
    }
    // write back
    file_put_contents($file, "<?php\n\nreturn " . var_export($arr, true) . ";\n");
}
