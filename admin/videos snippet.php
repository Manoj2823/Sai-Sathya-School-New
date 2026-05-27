<?php
// ══════════════════════════════════════════════════════════════
//  VIDEOS SNIPPET — use in samacheer.php and cbse.php
//
//  1. $school_name = 'samacheer'  OR  'cbse'  — change to suit the page
//  2. db.php already included ஆகியிருக்கணும் (top of the file-la)
// ══════════════════════════════════════════════════════════════

$school_name = 'samacheer'; // ← change this to 'cbse' in cbse.php

// ── Helper: YouTube URL → embed URL ──────────────────────────
function getEmbedUrl(string $url): string {
    if (preg_match('/youtu\.be\/([a-zA-Z0-9_\-]{11})/', $url, $m)) $id = $m[1];
    elseif (preg_match('/(?:v=|\/embed\/|\/shorts\/)([a-zA-Z0-9_\-]{11})/', $url, $m)) $id = $m[1];
    else return '';
    return "https://www.youtube.com/embed/{$id}?rel=0&modestbranding=1";
}

// ── Fetch videos from DB ──────────────────────────────────────
$videos = [];
if (!empty($conn)) {
    $sch_esc = mysqli_real_escape_string($conn, $school_name);
    $vid_res = mysqli_query($conn,
        "SELECT * FROM school_videos
         WHERE school='$sch_esc' AND is_active=1
         ORDER BY sort_order ASC, id ASC");
    while ($row = mysqli_fetch_assoc($vid_res)) {
        $videos[] = $row;
    }
}

// ── Fallback if DB empty ──────────────────────────────────────
if (empty($videos)) {
    $videos = [
        ['title' => 'School Highlights',  'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'description' => ''],
        ['title' => 'Annual Day 2024',    'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'description' => ''],
    ];
}
?>

<!-- ══════════════════════════════════════════════════════════
     PASTE THIS HTML BLOCK inside your gallery/video section
     ════════════════════════════════════════════════════════ -->

<section class="videos-section" id="videos">
    <div class="container">
        <h2 class="section-heading">Our Videos</h2>
        <p class="section-subtext">Watch our school life, events, and achievements</p>

        <div class="videos-grid">
            <?php foreach ($videos as $vid):
                $embed = getEmbedUrl($vid['youtube_url']);
                if (!$embed) continue;
            ?>
            <div class="video-item">
                <div class="video-wrapper">
                    <iframe
                        src="<?= htmlspecialchars($embed) ?>"
                        title="<?= htmlspecialchars($vid['title']) ?>"
                        loading="lazy"
                        allowfullscreen
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                    </iframe>
                </div>
                <div class="video-info">
                    <h4><?= htmlspecialchars($vid['title']) ?></h4>
                    <?php if (!empty($vid['description'])): ?>
                        <p><?= htmlspecialchars($vid['description']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     CSS — add this inside your <style> tag (or .css file)
     ════════════════════════════════════════════════════════ -->
<style>
.videos-section {
    padding: 60px 0;
    background: #f8fafc;         /* light bg — adjust to match your site */
}

.videos-section .section-heading {
    text-align: center;
    font-size: 2rem;
    color: #0a1f44;
    margin-bottom: 8px;
}

.videos-section .section-subtext {
    text-align: center;
    color: #64748b;
    margin-bottom: 40px;
    font-size: 1rem;
}

/* Grid: 3 columns on desktop, 2 on tablet, 1 on mobile */
.videos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
}

/* Responsive 16:9 iframe wrapper */
.video-wrapper {
    position: relative;
    width: 100%;
    padding-top: 56.25%;          /* 16 : 9 ratio */
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    background: #000;
}

.video-wrapper iframe {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    border: none;
}

.video-info {
    padding: 12px 4px 0;
}

.video-info h4 {
    font-size: 0.95rem;
    color: #0a1f44;
    font-weight: 700;
    margin-bottom: 4px;
}

.video-info p {
    font-size: 0.82rem;
    color: #64748b;
    line-height: 1.5;
}

/* ── Responsive ── */
@media (max-width: 900px) {
    .videos-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 580px) {
    .videos-grid { grid-template-columns: 1fr; }
}
</style>