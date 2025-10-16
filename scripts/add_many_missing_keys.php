<?php
$keysToAdd = [
    'ai_content_generation' => 'AI Content Generation',
    'enable_ai_content_generation' => 'Enable AI Content Generation',
    'social_login_settings' => 'Social Login Settings',
    'google_login' => 'Google Login',
    'facebook_login' => 'Facebook Login',
    'github_login' => 'GitHub Login',
    'enable_google_login' => 'Enable Google Login',
    'enable_facebook_login' => 'Enable Facebook Login',
    'enable_github_login' => 'Enable GitHub Login',
    'enter_google_client_id' => 'Enter Google Client ID',
    'enter_google_client_secret' => 'Enter Google Client Secret',
    'enter_facebook_client_id' => 'Enter Facebook Client ID',
    'enter_facebook_client_secret' => 'Enter Facebook Client Secret',
    'enter_github_client_id' => 'Enter GitHub Client ID',
    'enter_github_client_secret' => 'Enter GitHub Client Secret',
    'google_client_id' => 'Google Client ID',
    'google_client_secret' => 'Google Client Secret',
    'facebook_client_id' => 'Facebook Client ID',
    'facebook_client_secret' => 'Facebook Client Secret',
    'github_client_id' => 'GitHub Client ID',
    'github_client_secret' => 'GitHub Client Secret',
    'font_settings' => 'Font Settings',
    'frontend_font_settings' => 'Frontend Font Settings',
    'backend_font_settings' => 'Backend Font Settings',
    'font_family' => 'Font Family',
    'font_size' => 'Font Size',
    'font_weight' => 'Font Weight',
    'font_preview' => 'Font Preview',
    'font_size_small' => 'Small (12px)',
    'font_size_medium' => 'Medium (14px)',
    'font_size_large' => 'Large (16px)',
    'font_size_xlarge' => 'Extra Large (18px)',
    'use_google_fonts' => 'Use Google Fonts',
    'google_fonts_url' => 'Google Fonts URL',
    'enter_google_fonts_url' => 'Enter Google Fonts URL',
    'update' => 'Update',
    'normal' => 'Normal',
    'bold' => 'Bold',
    'bolder' => 'Bolder',
    'lighter' => 'Lighter',
    'arial' => 'Arial',
    'helvetica' => 'Helvetica',
    'times_new_roman' => 'Times New Roman',
    'georgia' => 'Georgia',
    'verdana' => 'Verdana',
    'tahoma' => 'Tahoma',
    'courier_new' => 'Courier New',
    'cairo' => 'Cairo'
];

$langFiles = [
    'resources/lang/en/app.php',
    'resources/lang/ar/app.php',
    'resources/lang/fr/app.php',
    'resources/lang/es/app.php'
];

foreach ($langFiles as $file) {
    if (!file_exists($file)) { echo "skip $file\n"; continue; }
    $arr = include $file;
    foreach ($keysToAdd as $k => $v) {
        if (!array_key_exists($k, $arr)) {
            if (strpos($file, '/ar/') !== false) {
                // basic Arabic mapping for a few keys, else copy English
                $arabMap = [
                    'ai_content_generation'=>'توليد المحتوى بالذكاء الاصطناعي',
                    'enable_ai_content_generation'=>'تفعيل توليد المحتوى بالذكاء الاصطناعي',
                    'social_login_settings'=>'إعدادات تسجيل الدخول الاجتماعي',
                    'google_login'=>'تسجيل دخول جوجل',
                    'facebook_login'=>'تسجيل دخول فيسبوك',
                    'github_login'=>'تسجيل دخول جِت هَب',
                    'enable_google_login'=>'تفعيل تسجيل دخول جوجل',
                    'enable_facebook_login'=>'تفعيل تسجيل دخول فيسبوك',
                    'enable_github_login'=>'تفعيل تسجيل دخول جِت هَب',
                    'font_settings'=>'إعدادات الخط',
                    'frontend_font_settings'=>'إعدادات خط الواجهة',
                    'backend_font_settings'=>'إعدادات خط لوحة التحكم',
                    'font_family'=>'نوع الخط',
                    'font_size'=>'حجم الخط',
                    'font_weight'=>'وزن الخط',
                    'font_preview'=>'معاينة الخط',
                    'use_google_fonts'=>'استخدام خطوط جوجل'
                ];
                $arr[$k] = $arabMap[$k] ?? $v;
            } else {
                // for fr/es use English placeholder
                $arr[$k] = $v;
            }
            echo "Added $k to $file\n";
        }
    }
    file_put_contents($file, "<?php\n\nreturn " . var_export($arr, true) . ";\n");
}

echo "Done\n";
