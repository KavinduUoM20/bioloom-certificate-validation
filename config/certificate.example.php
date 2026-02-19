<?php
/**
 * Certificate PDF layout – Bioloom Islands Pvt Ltd
 *
 * Copy to certificate.php and adjust. Your PDF is A5 landscape (210mm × 148mm).
 *
 * HOW TO MARK THE NAME AREA ON YOUR TEMPLATE
 * ------------------------------------------
 * 1. Open your certificate template in an image editor (Photoshop, GIMP, Canva, etc.).
 * 2. Make the canvas A5 landscape proportion: 210 × 148 (e.g. 2100×1480 px or 794×561 px).
 * 3. Decide where the recipient name should appear. Draw a rectangle there (or note the position).
 * 4. Measure from the TOP-LEFT of the page:
 *    - Left: distance in mm from the left edge to the start of the name area
 *    - Top:  distance in mm from the top edge to the baseline of the name
 *    - Width: width of the area for the name (name will be centred in this width)
 *    - Height: height reserved for the name (for font sizing)
 * 5. Enter those values below in 'name_area'. All values in millimetres (mm).
 *
 * Example: name centred in the middle of the page
 *   Left: 25, Top: 75, Width: 160, Height: 15
 * (25mm from left, 75mm from top, box 160mm wide × 15mm tall; name centred in the box)
 */
return [
    // Page: A5 landscape (210mm × 148mm). Do not change unless you need another size.
    'page_width_mm'  => 210,
    'page_height_mm' => 148,
    'orientation'    => 'L', // L = landscape

    // Name area: where to print the recipient name (in mm from top-left of page)
    'name_area' => [
        'left_mm'   => 25,   // distance from left edge to the name area
        'top_mm'    => 75,   // distance from top edge to the name (baseline)
        'width_mm'  => 160,  // width of the name box (name is centred in this)
        'height_mm' => 15,   // height of the name area (for line height)
    ],

    // Font size for the name (pt)
    'name_font_size' => 28,
];
