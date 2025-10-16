<?php
$view = file_get_contents(__DIR__ . '/../resources/views/backend/setting.blade.php');
preg_match_all('/trans\(\s*\'app\.([a-z0-9_]+)\'\s*\)/i', $view, $m);
$keys = array_unique($m[1]);
sort($keys);
echo "Found keys in view:\n";
foreach($keys as $k) echo "- $k\n";

// load languages
$langs = ['en','ar','fr','es'];
$missing = [];
foreach($langs as $lang){
    $file = __DIR__ . '/../resources/lang/' . $lang . '/app.php';
    if (!file_exists($file)) { $missing[$lang] = 'missing file'; continue; }
    $arr = include $file;
    foreach($keys as $k){
        if (!array_key_exists($k, $arr)){
            $missing[$lang][] = $k;
        }
    }
}

echo "\nMissing keys per language:\n";
foreach($langs as $lang){
    if (!isset($missing[$lang])){
        echo "$lang: none\n";
    } elseif ($missing[$lang] === 'missing file'){
        echo "$lang: language file missing\n";
    } else {
        echo "$lang: " . count($missing[$lang]) . " missing\n";
        foreach($missing[$lang] as $k) echo "  - $k\n";
    }
}
