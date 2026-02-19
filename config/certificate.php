<?php
/**
 * Certificate PDF layout – edit to match your template.
 * See config/certificate.example.php for how to mark the name area.
 */
return [
    'page_width_mm'  => 210,
    'page_height_mm' => 148,
    'orientation'    => 'L',

    'name_area' => [
    'left_mm'   => 27,
    'top_mm'    => 50,
    'width_mm'  => 131,
    'height_mm' => 13.8,
    ],

    'name_font_size' => 18,
];
