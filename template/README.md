# Certificate template – where to put it

Put your certificate design **in this folder**:  
`E:\2026\bioloom-cert-verification\template\`

## Exact file name and format

- **Name the file exactly:** `certificate-template.png` **or** `certificate-template.jpg`
- **Full path will be:**  
  `E:\2026\bioloom-cert-verification\template\certificate-template.png`  
  (or `.jpg` if you use a JPEG)

## Page size: A5 landscape

The generated PDF is **A5 landscape** (210 mm × 148 mm). Your template image should use the **same proportion** so it doesn’t stretch:

- **Ratio:** width : height = 210 : 148 (e.g. 2100×1480 px, or 1050×740 px, or 794×561 px).

## Marking the area where the name is printed

The recipient’s name is drawn in a **rectangle** you define in config, not in the centre by default.

1. Open **`config/certificate.php`** (copy from `config/certificate.example.php` if needed).
2. The **`name_area`** section defines where the name appears (all in **millimetres**, from the top-left of the page):
   - **left_mm** – distance from the left edge to the start of the name area
   - **top_mm** – distance from the top edge to where the name sits (baseline)
   - **width_mm** – width of the box; the name is **centred** in this width
   - **height_mm** – height reserved for the name (for line height)
3. **How to get these numbers:**  
   Open your template in an image editor. Use a ruler or guide in **mm**, or work from pixels (e.g. at 300 dpi, 1 mm ≈ 11.8 px). Draw a rectangle where the name should go and note:
   - left = distance from left edge to the left of the rectangle  
   - top = distance from top edge to the baseline of the text  
   - width = width of the rectangle  
   - height = height of the rectangle  

4. You can also change **`name_font_size`** (in points) in the same config file.

Example: name centred in the middle of an A5 landscape page (210×148 mm) would be roughly  
left 25, top 75, width 160, height 15. Adjust to match your design.
