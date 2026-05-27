<?php
// ══════════════════════════════════════════════════════════════
//  ADD THIS PHP BLOCK AT THE TOP of cbse.php
// ══════════════════════════════════════════════════════════════
require_once 'db.php';

// Fetch CBSE gallery from DB
$gallery = [];
if ($conn) {
    $gal_res = mysqli_query($conn, "SELECT * FROM gallery_images WHERE school='cbse' AND is_active=1 ORDER BY sort_order ASC");
    while ($row = mysqli_fetch_assoc($gal_res)) {
        $gallery[] = $row;
    }
}

// Fallback if DB empty
if (empty($gallery)) {
    $gallery = [
        ['image_path'=>'gallery/1.jpg',      'caption'=>'Campus Life 1'],
        ['image_path'=>'gallery/2.jpg',      'caption'=>'Campus Life 2'],
        ['image_path'=>'gallery/3.jpg',      'caption'=>'Campus Life 3'],
        ['image_path'=>'gallery/4.jpg',      'caption'=>'Campus Life 4'],
        ['image_path'=>'gallery/5.jpg',      'caption'=>'Campus Life 5'],
        ['image_path'=>'gallery/6.jpg',      'caption'=>'Campus Life 6'],
        ['image_path'=>'gallery/7.jpg',      'caption'=>'Campus Life 7'],
        ['image_path'=>'gallery/8.jpg',      'caption'=>'Campus Life 8'],
        ['image_path'=>'gallery/9.jpg',      'caption'=>'Campus Life 9'],
        ['image_path'=>'gallery/10.jpg',     'caption'=>'Campus Life 10'],
        ['image_path'=>'gallery/11.jpg',     'caption'=>'Campus Life 11'],
        ['image_path'=>'gallery/12(1).jpg',  'caption'=>'Campus Life 12'],
    ];
}
?>

<!-- GALLERY LOOP — use this in cbse.php -->
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