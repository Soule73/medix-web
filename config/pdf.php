<?php

return [
    'mode' => '',
    'format' => 'A4',
    'default_font_size' => '12',
    'default_font' => 'sans-serif',
    'margin_left' => 0,
    'margin_right' => 0,
    'margin_top' => 10,
    'margin_bottom' => 10,
    'margin_header' => 0,
    'margin_footer' => 0,
    'orientation' => 'P',
    'title' => 'Laravel mPDF',
    'subject' => '',
    'author' => '',
    'watermark' => '',
    'show_watermark' => false,
    'show_watermark_image' => true,
    'watermark_font' => 'sans-serif',
    'display_mode' => 'fullpage',
    'watermark_text_alpha' => 0.1,
    'watermark_image_path' => base_path('resources/assets/medix-logo.png'),
    'watermark_image_alpha' => 0.09,
    'watermark_image_size' => 'D',
    'watermark_image_position' => 'P',
    'auto_language_detection' => true,
    'temp_dir' => storage_path('app'),
    'pdfa' => false,
    'pdfaauto' => false,
    'use_active_forms' => false,

    'custom_font_dir' => base_path('resources/fonts/'),
    'custom_font_data' => [
        'rubik' => [ // must be lowercase and snake_case
            'R' => 'Rubik.ttf',    // regular font
            // 'B'  => 'ExampleFont-Bold.ttf',       // optional: bold font
            // 'I'  => 'ExampleFont-Italic.ttf',     // optional: italic font
            // 'BI' => 'ExampleFont-Bold-Italic.ttf' // optional: bold-italic font
        ],
    ],
];
