<?php
// ══════════════════════════════════════════════════════════════
//  ADD THIS PHP BLOCK AT THE TOP of samacheer.php
//  (replace any existing gallery array)
// ══════════════════════════════════════════════════════════════
require_once 'db.php';

// Fetch Samacheer gallery from DB
$gallery = [];
if ($conn) {
    $gal_res = mysqli_query($conn, "SELECT * FROM gallery_images WHERE school='samacheer' AND is_active=1 ORDER BY sort_order ASC");
    while ($row = mysqli_fetch_assoc($gal_res)) {
        $gallery[] = $row;
    }
}

// Fallback if DB empty
if (empty($gallery)) {
    $gallery = [
        ['image_path'=>'gallery/1.jpg',      'caption'=>'School Life 1'],
        ['image_path'=>'gallery/2.jpg',      'caption'=>'School Life 2'],
        ['image_path'=>'gallery/3.jpg',      'caption'=>'School Life 3'],
        ['image_path'=>'gallery/4.jpg',      'caption'=>'School Life 4'],
        ['image_path'=>'gallery/5.jpg',      'caption'=>'School Life 5'],
        ['image_path'=>'gallery/6.jpg',      'caption'=>'School Life 6'],
        ['image_path'=>'gallery/7.jpg',      'caption'=>'School Life 7'],
        ['image_path'=>'gallery/8.jpg',      'caption'=>'School Life 8'],
        ['image_path'=>'gallery/9.jpg',      'caption'=>'School Life 9'],
        ['image_path'=>'gallery/10.jpg',     'caption'=>'School Life 10'],
        ['image_path'=>'gallery/11.jpg',     'caption'=>'School Life 11'],
        ['image_path'=>'gallery/12(1).jpg',  'caption'=>'School Life 12'],
    ];
}
?>

<!-- ══════════════════════════════════════════════════════════
     IN YOUR HTML — find the gallery section and
     REPLACE the static <img> tags with this PHP loop:
     ════════════════════════════════════════════════════════ -->

<!-- GALLERY SECTION — use this PHP loop -->
<?php foreach ($gallery as $img): ?>
  <div class="gallery-item">
    <img src="<?= htmlspecialchars($img['image_path']) ?>"
         alt="<?= htmlspecialchars($img['caption']) ?>"
         loading="lazy">
    <?php if ($img['caption']): ?>
      <div class="gallery-caption"><?= htmlspecialchars($img['caption']) ?></div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>