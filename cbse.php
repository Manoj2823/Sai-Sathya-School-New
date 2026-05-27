<?php
require_once 'db.php';

// Fetch page-hero content from DB
$hero_res = mysqli_query($conn, "SELECT * FROM hero_sections WHERE school = 'cbse' LIMIT 1");
$hero = mysqli_fetch_assoc($hero_res);
// Fallback if no DB row yet
if (!$hero) {
    $hero = [
        'title' => 'Sri Sathya Sai Vidya Vihar',
        'description' => 'Education with Human Values. A modern CBSE institution dedicated to academic excellence, discipline, spirituality, and holistic development.',
    ];
}

// Fetch gallery images from DB
$gallery_res = mysqli_query($conn, "SELECT * FROM gallery_images WHERE school = 'cbse' AND is_active = 1 ORDER BY sort_order ASC");
$gallery_images = [];
while ($row = mysqli_fetch_assoc($gallery_res)) {
    $gallery_images[] = $row;
}
// Fallback to static images if DB is empty
if (empty($gallery_images)) {
    for ($i = 1; $i <= 11; $i++) {
        $gallery_images[] = ['image_path' => "gallery/{$i}.jpg", 'caption' => "Gallery Image {$i}"];
    }
    $gallery_images[] = ['image_path' => 'gallery/12(1).jpg', 'caption' => 'Gallery Image 12'];
}
require_once 'admin/db.php';

// CBSE page-kaana contents-a fetch panrom
$content_query = mysqli_query($conn, "SELECT * FROM page_contents WHERE page_name='cbse'");

// ── Fetch Videos from DB ──────────────────────────────────────
if (!function_exists('getEmbedUrl')) {
    function getEmbedUrl(string $url): string {
        if (preg_match('/youtu\.be\/([a-zA-Z0-9_\-]{11})/', $url, $m)) $id = $m[1];
        elseif (preg_match('/(?:v=|\/embed\/|\/shorts\/)([a-zA-Z0-9_\-]{11})/', $url, $m)) $id = $m[1];
        else return '';
        return "https://www.youtube.com/embed/{$id}?rel=0&modestbranding=1";
    }
}
$school_videos = [];
$vid_res = mysqli_query($conn, "SELECT * FROM school_videos WHERE school='cbse' AND is_active=1 ORDER BY sort_order ASC, id ASC");
if ($vid_res) {
    while ($row = mysqli_fetch_assoc($vid_res)) $school_videos[] = $row;
}
$cbse_contents = [];
if ($content_query) {
    while ($row = mysqli_fetch_assoc($content_query)) {
        if (!isset($row['is_active']) || $row['is_active'] == 1) {
            $cbse_contents[$row['section_title']] = $row['content_text'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sri Sathya Sai Vidya Vihar – CBSE School</title>
    <link rel="icon" type="image/png" href="images/academiya_heading_logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&family=Cinzel:wght@400;600&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --navy: #0a1f44;
            --deep: #071530;
            --gold: #c8932a;
            --gold-lt: #f0c060;
            --cream: #fdf6ec;
            --white: #ffffff;
            --text: #2c2c2c;
            --muted: #6b7280;
            --shadow: 0 8px 32px rgba(10, 31, 68, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lato', sans-serif;
            background: var(--cream);
            color: var(--text);
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        img {
            max-width: 100%;
            display: block;
        }

        /* TOP BAR */

        .top-bar {
            background: var(--deep);
            padding: 6px 0;
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.65);
        }

        .top-bar .inner {
            max-width: 1200px;
            margin: auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }

        .top-bar a {
            color: var(--gold-lt);
            transition: color .2s;
        }

        .top-bar a:hover {
            color: #fff;
        }

        /* NAVBAR */

        nav {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: var(--navy);
            box-shadow: 0 2px 20px rgba(0, 0, 0, .35);
        }

        nav.scrolled {
            background: rgba(7, 21, 48, .97);
            backdrop-filter: blur(10px);
        }

        .nav-inner {
            max-width: 1200px;
            margin: auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--gold), var(--gold-lt));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cinzel', serif;
            font-size: 1.2rem;
            color: var(--deep);
            font-weight: 600;
            flex-shrink: 0;
        }

        .brand-text span:first-child {
            display: block;
            font-family: 'Playfair Display', serif;
            font-size: 0.98rem;
            color: var(--white);
            font-weight: 600;
        }

        .brand-text span:last-child {
            display: block;
            font-size: .7rem;
            color: var(--gold-lt);
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .nav-links a {
            color: rgba(255, 255, 255, .82);
            font-size: 0.85rem;
            padding: 8px 14px;
            border-radius: 6px;
            transition: all .25s;
            position: relative;
            letter-spacing: .04em;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: var(--gold-lt);
            border-radius: 2px;
            transition: width .3s;
        }

        .nav-links a:hover {
            color: var(--white);
            background: rgba(255, 255, 255, .07);
        }

        .nav-links a:hover::after {
            width: 60%;
        }

        .nav-links a.active {
            color: var(--gold-lt);
        }

        .nav-links a.active::after {
            width: 60%;
        }

        .nav-cta {
            background: linear-gradient(135deg, var(--gold), #e8a030) !important;
            color: var(--deep) !important;
            font-weight: 700 !important;
            padding: 8px 18px !important;
            border-radius: 6px !important;
        }

        .nav-cta:hover {
            box-shadow: 0 4px 15px rgba(200, 147, 42, .45) !important;
            transform: translateY(-1px);
        }

        .nav-cta::after {
            display: none !important;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 6px;
            border: none;
            background: none;
        }

        .hamburger span {
            display: block;
            width: 24px;
            height: 2px;
            background: var(--white);
            border-radius: 2px;
            transition: all .35s;
        }

        .hamburger.open span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .hamburger.open span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.open span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }

        /* HERO */

        .page-hero {
            background: linear-gradient(135deg, var(--navy) 0%, #0d2960 100%);
            padding: 40px 0 32px;
            position: relative;
            overflow: hidden;
        }

        .page-hero::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(200, 147, 42, .14) 0%, transparent 70%);
            border-radius: 50%;
        }

        .page-hero::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gold), var(--gold-lt), var(--gold));
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 0 20px;
        }

        .hero-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .78rem;
            color: rgba(255, 255, 255, .55);
            margin-bottom: 14px;
        }

        .hero-breadcrumb a {
            color: var(--gold-lt);
        }

        .hero-breadcrumb span {
            color: rgba(255, 255, 255, .3);
        }

        .page-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 3.5vw, 2.8rem);
            color: #fff;
            margin-bottom: 10px;
        }

        .page-hero p {
            color: rgba(255, 255, 255, .65);
            font-size: 1rem;
            max-width: 560px;
            line-height: 1.7;
        }

        /* ABOUT */

        .about-section {
            padding: 80px 0;
            background: #fff;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 420px 1fr;
            gap: 50px;
            align-items: center;
        }

        .about-image img {
            width: 100%;
            height: 320px;
            object-fit: cover;
        }

        .about-image {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .about-content h2 {
            font-family: 'Playfair Display', serif;
            color: var(--navy);
            font-size: 2rem;
            margin-bottom: 12px;
        }

        .divider {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--gold-lt));
            margin-bottom: 24px;
        }

        .about-content p {
            line-height: 1.9;
            margin-bottom: 18px;
            color: #444;
        }

        .highlight-box {
            background: linear-gradient(135deg, var(--navy), #0d2960);
            padding: 24px;
            border-radius: 14px;
            margin-top: 20px;
        }

        .highlight-box p {
            color: #ffffff !important;
            margin: 0 !important;
            font-style: italic;
        }

        .section-tag {
            display: inline-block;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: var(--gold);
            background: rgba(200, 147, 42, .1);
            border: 1px solid rgba(200, 147, 42, .3);
            padding: 4px 14px;
            border-radius: 99px;
            margin-bottom: 12px;
        }

        /* ACADEMICS */

        .academics-lead {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 52px;
            align-items: start;
        }

        .academics-lead .side-img {
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .academics-lead .side-img img {
            width: 100%;
            height: 260px;
            object-fit: cover;
        }

        .approach-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .approach-card {
            background: var(--white);
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 3px 16px rgba(10, 31, 68, .07);
            border-top: 3px solid var(--gold);
            transition: transform .3s;
        }

        .approach-card:hover {
            transform: translateY(-4px);
        }

        .approach-card .num {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: rgba(200, 147, 42, .25);
            font-weight: 700;
            margin-bottom: 8px;
        }

        .approach-card h3 {
            color: var(--navy);
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .approach-card p {
            color: var(--muted);
            line-height: 1.7;
            font-size: .9rem;
        }

        h2.section-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.5rem, 2.5vw, 2.1rem);
            color: var(--navy);
            line-height: 1.3;
            margin-bottom: 10px;
            text-align: left;
        }

        .section-divider {
            width: 56px;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--gold-lt));
            border-radius: 2px;
            margin: 12px 0 24px;
        }


        /* CAMPUS */
        .campus-intro p {
            font-size: .96rem;
            line-height: 1.85;
            color: #444;
            margin-bottom: 14px;
        }

        .campus-list {
            list-style: none;
            margin: 24px 0 36px;
            display: grid;
            gap: 16px;
        }

        .campus-list li {
            position: relative;
            padding-left: 28px;
            font-size: .95rem;
            color: #444;
            line-height: 1.6;
        }

        .campus-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            top: 0;
            color: var(--gold);
            font-weight: bold;
        }

        .community-block {
            background: linear-gradient(135deg, var(--navy), #0d2960);
            border-radius: 16px;
            padding: 40px 44px;
            margin-top: 40px;
        }

        .community-block h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--gold-lt);
            margin-bottom: 16px;
        }

        .community-block p {
            font-size: .93rem;
            line-height: 1.8;
            color: rgba(255, 255, 255, .75);
            margin-bottom: 14px;
        }

        /* GALLERY */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            align-items: start;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            aspect-ratio: 4/3;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(10, 31, 68, .12);
            background: #eee;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.08);
        }

        .gallery-item .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(7, 21, 48, .8) 0%, transparent 50%);
            opacity: 0;
            transition: opacity .3s;
            display: flex;
            align-items: flex-end;
            padding: 16px;
        }

        .gallery-item:hover .overlay {
            opacity: 1;
        }

        .overlay span {
            color: #fff;
            font-size: .82rem;
            font-weight: 600;
        }

        /* Lightbox */
        .lightbox {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(7, 21, 48, .95);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .lightbox.open {
            display: flex;
        }

        .lightbox img {
            max-width: 90vw;
            max-height: 85vh;
            border-radius: 8px;
            object-fit: contain;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .6);
        }

        .lb-close {
            position: absolute;
            top: 20px;
            right: 24px;
            font-size: 2rem;
            color: rgba(255, 255, 255, .7);
            cursor: pointer;
            transition: color .2s;
            background: none;
            border: none;
        }

        .lb-close:hover {
            color: var(--gold-lt);
        }

        .lb-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .2);
            color: #fff;
            font-size: 1.3rem;
            cursor: pointer;
            transition: all .3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lb-nav:hover {
            background: var(--gold);
            border-color: var(--gold);
            color: var(--deep);
        }

        .lb-prev {
            left: 16px;
        }

        .lb-next {
            right: 16px;
        }

        /* CAREER */
        .career-hero-block {
            background: linear-gradient(135deg, var(--navy), #0d2960);
            border-radius: 16px;
            padding: 52px 44px;
            display: flex;
            align-items: center;
            gap: 40px;
            flex-wrap: wrap;
        }

        .career-hero-block .text {
            flex: 1;
            min-width: 240px;
        }

        .career-hero-block h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #fff;
            margin-bottom: 14px;
        }

        .career-hero-block p {
            color: rgba(255, 255, 255, .72);
            line-height: 1.8;
            font-size: .95rem;
            margin-bottom: 20px;
        }

        .career-hero-block .icon-badge {
            font-size: 5rem;
            flex-shrink: 0;
        }

        /* ADMISSIONS */
        .admissions-banner {
            background: linear-gradient(135deg, var(--gold), #e8a030);
            border-radius: 16px;
            padding: 40px 44px;
            text-align: center;
            margin-bottom: 48px;
            position: relative;
            overflow: hidden;
        }

        .admissions-banner h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--deep);
            margin-bottom: 8px;
        }

        .admissions-banner p {
            color: rgba(7, 21, 48, .7);
            font-size: 1rem;
            margin-bottom: 22px;
        }

        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--navy), #0d2960);
            color: var(--gold-lt);
            background: linear-gradient(135deg, var(--gold), #e8a030);
            color: var(--deep);
            font-weight: 700;
            font-size: .9rem;
            padding: 13px 28px;
            border-radius: 8px;
            letter-spacing: .05em;
            transition: all .3s;
            box-shadow: 0 4px 18px rgba(10, 31, 68, .35);
            box-shadow: 0 4px 18px rgba(200, 147, 42, .35);
        }

        .cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 26px rgba(200, 147, 42, .5);
        }

        .admissions-banner .cta-btn {
            background: linear-gradient(135deg, var(--navy), #0d2960);
            color: var(--gold-lt);
            box-shadow: 0 4px 18px rgba(10, 31, 68, .35);
        }

        .admissions-banner .cta-btn:hover {
            box-shadow: 0 8px 26px rgba(10, 31, 68, .5);
        }

        .admissions-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 36px;
            align-items: start;
        }

        .contact-card {
            background: var(--white);
            border-radius: 14px;
            padding: 32px;
            box-shadow: var(--shadow);
        }

        .contact-card h3,
        .age-table-wrap h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            color: var(--navy);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid rgba(200, 147, 42, .2);
        }

        .contact-row {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .contact-row .icon {
            width: 38px;
            height: 38px;
            background: rgba(200, 147, 42, .1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .contact-row .info {
            flex: 1;
        }

        .contact-row .info strong {
            display: block;
            font-size: .82rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .1em;
            margin-bottom: 4px;
        }

        .contact-row .info span {
            font-size: .9rem;
            color: var(--text);
            line-height: 1.5;
        }

        .contact-row .info a {
            color: var(--navy);
            font-weight: 700;
        }

        .age-table-wrap {
            background: var(--white);
            border-radius: 14px;
            padding: 32px;
            box-shadow: var(--shadow);
        }

        .age-table {
            width: 100%;
            border-collapse: collapse;
        }

        .age-table th {
            background: linear-gradient(135deg, var(--navy), #0d2960);
            color: var(--gold-lt);
            padding: 12px 16px;
            text-align: left;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .1em;
        }

        .age-table th:first-child {
            border-radius: 8px 0 0 0;
        }

        .age-table th:last-child {
            border-radius: 0 8px 0 0;
        }

        .age-table td {
            padding: 11px 16px;
            font-size: .88rem;
            color: var(--text);
            border-bottom: 1px solid rgba(10, 31, 68, .05);
        }

        .age-table tr:nth-child(even) td {
            background: rgba(10, 31, 68, .025);
        }

        .age-table tr:hover td {
            background: rgba(200, 147, 42, .06);
        }

        .age-badge {
            display: inline-block;
            background: rgba(200, 147, 42, .12);
            color: var(--gold);
            font-weight: 700;
            font-size: .78rem;
            padding: 3px 10px;
            border-radius: 99px;
        }

        /* Forms */
        .theme-form {
            background: var(--white);
            border-radius: 14px;
            padding: 40px;
            box-shadow: var(--shadow);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: .85rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid rgba(10, 31, 68, .15);
            border-radius: 8px;
            font-size: .95rem;
            font-family: inherit;
            color: var(--text);
            background: rgba(10, 31, 68, .02);
            transition: all .3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(200, 147, 42, .15);
            background: var(--white);
        }

        .theme-form button[type="submit"] {
            border: none;
            cursor: pointer;
            font-family: inherit;
            margin-top: 10px;
            width: 100%;
            justify-content: center;
        }

        /* FEATURES */

        .features {
            padding: 80px 0;
            background: var(--cream);
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: var(--navy);
            margin-bottom: 12px;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 48px;
        }

        .value-card {
            background: var(--white);
            border-radius: 14px;
            padding: 28px 24px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(10, 31, 68, .07);
            border: 1px solid rgba(10, 31, 68, .06);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 40px rgba(10, 31, 68, .15);
        }

        .value-icon {
            font-size: 2rem;
            margin-bottom: 14px;
        }

        .value-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
            color: var(--navy);
            margin-bottom: 8px;
        }

        .value-card p {
            font-size: .82rem;
            color: var(--muted);
            line-height: 1.6;
        }

        /* REVEAL */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity .65s ease, transform .65s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: none;
        }

        .reveal-left {
            opacity: 0;
            transform: translateX(-36px);
            transition: opacity .65s ease, transform .65s ease;
        }

        .reveal-right {
            opacity: 0;
            transform: translateX(36px);
            transition: opacity .65s ease, transform .65s ease;
        }

        .reveal-left.visible,
        .reveal-right.visible {
            opacity: 1;
            transform: none;
        }

        .delay-1 {
            transition-delay: .1s;
        }

        .delay-2 {
            transition-delay: .2s;
        }

        .delay-3 {
            transition-delay: .3s;
        }

        /* VIDEOS SECTION */
        .videos-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }
        .video-wrapper {
            position: relative;
            width: 100%;
            padding-top: 56.25%;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.14);
            background: #000;
        }
        .video-wrapper iframe {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            border: none;
        }
        .video-info { padding: 12px 4px 0; }
        .video-info h4 { font-size: .95rem; color: var(--navy); font-weight: 700; margin-bottom: 4px; }
        .video-info p  { font-size: .82rem; color: var(--muted); line-height: 1.5; }
        @media(max-width:900px) { .videos-grid { grid-template-columns: repeat(2,1fr); } }
        @media(max-width:580px) { .videos-grid { grid-template-columns: 1fr; } }

        /* FOOTER */

        footer {
            background: linear-gradient(160deg, var(--deep), var(--navy));
            padding: 50px 0 20px;
            color: #fff;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
        }

        .footer-col h3 {
            color: var(--gold-lt);
            margin-bottom: 16px;
            font-family: 'Playfair Display', serif;
        }

        .footer-col p,
        .footer-col a {
            color: rgba(255, 255, 255, .7);
            line-height: 1.9;
            font-size: .9rem;
            display: block;
        }

        .footer-col a:hover {
            color: var(--gold-lt);
        }

        .social-row {
            display: flex;
            gap: 10px;
            margin-top: 14px;
        }

        .social-row .soc-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            display: flex !important;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            color: rgba(255, 255, 255, .65);
            line-height: 1;
            transition: all .3s;
        }

        .social-row .soc-btn:hover {
            background: var(--gold);
            border-color: var(--gold);
            color: var(--deep);
            transform: translateY(-2px);
        }

        .footer-bottom {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, .1);
            color: rgba(255, 255, 255, .45);
            font-size: .85rem;
        }

        .footer-bottom a {
            color: var(--gold-lt);
        }

        /* PANELS */
        .page-panel {
            display: none;
        }

        .page-panel.active {
            display: block;
        }

        /* RESPONSIVE */

        /* 1. Tablet & Small Desktops (Includes 1030px issue) */
         @media(max-width:1200px) {
            .nav-links {
                display: none;
                flex-direction: column;
                gap: 4px;
            }

            .nav-links.open {
                display: flex;
                position: absolute;
                top: 64px;
                left: 0;
                right: 0;
                background: var(--deep);
                padding: 12px 16px 20px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, .3);
                z-index: 999;
            }

            .nav-links.open li:last-child {
                margin-top: 16px;
            }

            .hamburger {
                display: flex;
            }

            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }

            .about-img-wrap img {
                width: 100% !important;
                height: auto !important;
                max-height: 400px !important;
                object-fit: cover !important;
                object-position: top center !important;
            }
        }

        /* 2. Then 900px (Large Phones & Small Tablets) */
        @media(max-width:900px) {
            .about-grid,
            .academics-lead,
            .admissions-layout {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr; /* Ippo ithu correct-a override aagum */
            }

            .career-hero-block {
                padding: 36px 28px;
            }

            .community-block {
                padding: 32px 28px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .theme-form {
                padding: 24px;
            }
        }

        /* 3. Finally 480px (Small Phones) */
        @media(max-width:480px) {
            .admissions-banner {
                padding: 30px 22px;
            }

            .contact-card,
            .age-table-wrap {
                padding: 22px;
            }
        }
        

        @media(max-width:480px) {
            .admissions-banner {
                padding: 30px 22px;
            }

            .contact-card,
            .age-table-wrap {
                padding: 22px;
            }
        }

        /* ── VIDEOS GRID ── */
        .videos-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }
        .video-wrapper {
            position: relative;
            width: 100%;
            padding-top: 56.25%;
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
        .video-info { padding: 12px 4px 0; }
        .video-info h4 { font-size: .95rem; color: var(--navy); font-weight: 700; margin-bottom: 4px; }
        .video-info p { font-size: .82rem; color: var(--muted); line-height: 1.5; }
        @media(max-width:900px) { .videos-grid { grid-template-columns: repeat(2,1fr); } }
        @media(max-width:580px) { .videos-grid { grid-template-columns: 1fr; } }

        /* ── FLOATING CTA ─────────────────────────────────────────────── */
        .float-cta {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 888;
            background: linear-gradient(135deg, var(--navy), #0d2960);
            color: var(--gold-lt);
            font-weight: 700;
            font-size: .8rem;
            padding: 11px 18px;
            border-radius: 50px;
            box-shadow: 0 6px 22px rgba(10, 31, 68, .35);
            display: flex;
            align-items: center;
            gap: 6px;
            opacity: 0;
            pointer-events: none;
            transition: all .3s;
            text-decoration: none;
        }

        .float-cta.show {
            opacity: 1;
            pointer-events: all;
        }

        .float-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(10, 31, 68, .55);
        }

        /* ── VIDEOS GRID ── */
        .videos-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }
        .video-wrapper {
            position: relative;
            width: 100%;
            padding-top: 56.25%;
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
        .video-info { padding: 12px 4px 0; }
        .video-info h4 { font-size: .95rem; color: var(--navy); font-weight: 700; margin-bottom: 4px; }
        .video-info p { font-size: .82rem; color: var(--muted); line-height: 1.5; }
        @media(max-width:900px) { .videos-grid { grid-template-columns: repeat(2,1fr); } }
        @media(max-width:580px) { .videos-grid { grid-template-columns: 1fr; } }
    </style>
</head>

<body>

    <!-- FLOATING CTA -->
    <a href="http://sathyasaischools.org/previous/contactus1.php" target="_blank" class="float-cta" id="floatCta">🎓
        Apply Now</a>
    <a href="#admissions" class="float-cta" id="floatCta"
        onclick="switchTab('admissions'); setTimeout(() => document.getElementById('registration-form').scrollIntoView({behavior: 'smooth'}), 50); return false;">🎓
        Apply Now</a>

    <!-- NAVBAR -->

    <nav id="navbar">
        <div class="nav-inner">

            <div class="nav-brand">
                <img src="images/cbse_logo.png" alt="CBSE Logo" class="brand-icon">

                <div class="brand-text">
                    <span>Sri Sathya Sai Vidya Vihar</span>
                    <span>Thiruvottiyur, Chennai</span>
                </div>
            </div>

            <ul class="nav-links" id="navLinks">
                <li><a href="index.php">Home</a></li>
                <li><a href="#" class="active" data-nav="about" onclick="switchTab('about'); return false;">CBSE
                        School</a></li>
                <li><a href="#" data-nav="academics" onclick="switchTab('academics'); return false;">Academics</a></li>
                <li><a href="#" data-nav="campus" onclick="switchTab('campus'); return false;">Campus &amp;
                        Community</a></li>
                <li><a href="#" data-nav="gallery" onclick="switchTab('gallery'); return false;">Gallery</a></li>
                <li><a href="#" data-nav="videos" onclick="switchTab('videos'); return false;">Videos</a></li>
                <li><a href="#" data-nav="career" onclick="switchTab('career'); return false;">Career With Us</a></li>
                <li><a href="#" data-nav="admissions" onclick="switchTab('admissions'); return false;"
                        class="nav-cta">New Admissions</a></li>
            </ul>

            <button class="hamburger" id="hamburger" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>

        </div>
    </nav>

    <!-- PAGE HERO -->
    <div class="page-hero" id="pageHero">
        <div class="container">
            <div class="hero-breadcrumb">
                <a href="index.php">Home</a>
                <span>›</span>
                <span id="heroBreadcrumb">CBSE School</span>
            </div>
            <h1 id="heroTitle"><?= htmlspecialchars($hero['title']) ?></h1>
            <p id="heroDesc"><?= htmlspecialchars($hero['description']) ?></p>
        </div>
    </div>

    <!-- PANELS WRAPPER -->
    <div class="page-panel active" id="panel-about">

        <!-- ABOUT -->

        <section class="about-section">

            <div class="container">

                <div class="about-grid">

                    <div class="about-image reveal-left">
                        <img src="images/cbse_school_img.png" alt="CBSE School">
                    </div>

                    <div class="about-content reveal-right">
                        <span class="section-tag"><?= $cbse_contents['about_tag'] ?? 'CBSE School' ?></span>

                        <h2><?= $cbse_contents['about_title'] ?? 'Sri Sathya Sai Vidya Vihar <br>CBSE Syllabus' ?></h2>

                        <div class="divider"></div>

                        <p><?= $cbse_contents['about_para1'] ?? 'By the Divine Grace and Blessings of Bhagawan Sri Sathya Sai Baba, the Sri Sathya Sai Vidya Vihar, an elite value-based CBSE school, was launched in Thiruvottiyur in June 2016 for classes from Pre KG to Grade 10.' ?></p>

                        <p><?= $cbse_contents['about_para2'] ?? 'In a sprawling green campus with modern infrastructure, spacious classrooms, laboratories, sports facilities and activity centers, the school nurtures students to become physically strong, mentally balanced, morally upright, academically excellent and spiritually enlightened.' ?></p>

                        <div class="highlight-box">
                            <p><?= $cbse_contents['about_quote'] ?? '“Education is for life, not merely for a living.”' ?></p>
                        </div>

                    </div>

                </div>

            </div>

        </section>

        <!-- FEATURES -->

        <section class="features">

            <div class="container">

                <div class="section-title reveal">
                    <span class="section-tag">Our Foundation</span>
                    <h2>Four Pillars of Sai Educare</h2>
                    <div class="divider" style="margin:12px auto 0;"></div>
                </div>

                <div class="values-grid">
                    <div class="value-card reveal delay-1">
                        <div class="value-icon">🌟</div>
                        <h3><?= $cbse_contents['value1_name'] ?? 'Sathya' ?></h3>
                        <p><?= $cbse_contents['value1_desc'] ?? 'Truth — the foundation of all learning and character. Students are encouraged to embrace honesty in thought, word and deed.' ?></p>
                    </div>
                    <div class="value-card reveal delay-2">
                        <div class="value-icon">⚖️</div>
                        <h3><?= $cbse_contents['value2_name'] ?? 'Dharma' ?></h3>
                        <p><?= $cbse_contents['value2_desc'] ?? 'Righteous conduct — moral and ethical values integrated into every aspect of school life and the curriculum.' ?></p>
                    </div>
                    <div class="value-card reveal delay-3">
                        <div class="value-icon">🕊️</div>
                        <h3><?= $cbse_contents['value3_name'] ?? 'Santhi' ?></h3>
                        <p><?= $cbse_contents['value3_desc'] ?? 'Peace — inner tranquility cultivated through meditation, yoga, and a calm, nurturing school environment.' ?></p>
                    </div>
                    <div class="value-card reveal delay-1">
                        <div class="value-icon">❤️</div>
                        <h3><?= $cbse_contents['value4_name'] ?? 'Prema' ?></h3>
                        <p><?= $cbse_contents['value4_desc'] ?? 'Love — unconditional love for all beings, fostering compassion, unity of faiths, and care for the community.' ?></p>
                    </div>
                </div>

            </div>

        </section>

    </div>

    <!-- PLACEHOLDER PANELS -->
    <div class="page-panel" id="panel-academics">
        <section class="about-section">
            <div class="container">
                <div class="reveal" style="margin-bottom:40px;">
                    <span class="section-tag"><?= $cbse_contents['academics_tag'] ?? 'Academics' ?></span>
                    <h2 class="section-title"><?= $cbse_contents['academics_title'] ?? 'Holistic Academic Programme' ?></h2>
                    <div class="section-divider"></div>
                </div>
                <div class="academics-lead">
                    <div class="side-img reveal-left">
                        <img src="images/cbse_school_img.png" alt="Academics at CBSE">
                    </div>
                    <div class="about-content reveal-right">
                        <p><?= $cbse_contents['academics_para1'] ?? 'Sri Sathya Sai Vidya Vihar offers a comprehensive CBSE curriculum designed to foster a love for learning. Our academic program is rigorous yet flexible, accommodating the diverse needs and learning styles of our students.' ?></p>
                        <p><?= $cbse_contents['academics_para2'] ?? 'We focus on building strong foundational skills while encouraging critical thinking, creativity, and problem-solving. Our dedicated faculty employs innovative teaching methodologies to make learning an engaging and meaningful experience.' ?></p>
                    </div>
                </div>

                <div style="margin-top: 60px; text-align: center;" class="reveal">
                    <h2><?= $cbse_contents['approach_title'] ?? 'Our Approach' ?></h2>
                    <div class="divider" style="margin: 12px auto 0;"></div>
                </div>

                <div class="approach-grid">
                    <div class="approach-card reveal delay-1">
                        <div class="num">01</div>
                        <h3><?= $cbse_contents['approach1_name'] ?? 'Integrated Learning' ?></h3>
                        <p><?= $cbse_contents['approach1_desc'] ?? 'We blend subjects and values to create a holistic learning environment, ensuring students understand the interconnectedness of knowledge.' ?></p>
                    </div>
                    <div class="approach-card reveal delay-2">
                        <div class="num">02</div>
                        <h3><?= $cbse_contents['approach2_name'] ?? 'Experiential Education' ?></h3>
                        <p><?= $cbse_contents['approach2_desc'] ?? 'Hands-on activities, projects, and field trips bring concepts to life, helping students apply theoretical knowledge to real-world situations.' ?></p>
                    </div>
                    <div class="approach-card reveal delay-3">
                        <div class="num">03</div>
                        <h3><?= $cbse_contents['approach3_name'] ?? 'Continuous Assessment' ?></h3>
                        <p><?= $cbse_contents['approach3_desc'] ?? "Regular evaluations and constructive feedback guide students' progress, focusing on their overall growth rather than just grades." ?></p>
                    </div>
                    <div class="approach-card reveal delay-1">
                        <div class="num">04</div>
                        <h3><?= $cbse_contents['approach4_name'] ?? 'Technology in Classroom' ?></h3>
                        <p><?= $cbse_contents['approach4_desc'] ?? 'Smart classrooms and digital resources are integrated into daily lessons to enhance understanding and prepare students for the future.' ?></p>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <div class="page-panel" id="panel-campus">
        <section class="about-section">
            <div class="container">
                <div class="reveal" style="margin-bottom:36px;">
                    <span class="section-tag">Campus &amp; Community</span>
                    <h2 class="section-title">A Place Where Every Child Thrives</h2>
                    <div class="section-divider"></div>
                </div>
                <div class="campus-intro reveal">
                    <p>We believe that every child is unique. Each one has a different pace of learning, which needs to
                        be discovered by the school.</p>
                    <p>Therefore, Sathya Sai Schools understand and fulfil the individual needs of each and every
                        learner.</p>
                    <p>We tickle the imagination of our children so that they can grasp faster. We encourage them to
                        explore, experience, experiment and discover their own path. We provide them with a stimulating
                        environment where love, affection and joy brings out the best in them.</p>
                    <p>We have an education system that builds the character of its students and empowers them in all
                        aspects of mental, physical, emotional, social and spiritual growth.</p>

                    <div class="highlight-box" style="margin: 24px 0;">
                        <p>“The kids in our classroom are infinitely more significant than the subject matter we teach.”
                        </p>
                    </div>

                    <ul class="campus-list">
                        <li>With a maximum of 30 students in a class, every child receives the personal attention that
                            he/she deserves and needs.</li>
                        <li>A broad range of subject areas are taught to provide children with sufficient flexibility
                            when choosing their area of specialization.</li>
                        <li>Special emphasis is placed on learning Languages to enable the students master the
                            languages.</li>
                        <li>Exposures to National and International issues to increase students awareness.</li>
                        <li>Innovative problem solving techniques, questioning and active participation in discussions
                            are strongly encouraged to build curiosity and confidence.</li>
                    </ul>
                </div>

                <div class="community-block reveal">
                    <h2>Community Matters</h2>
                    <p>The relationship between home and school plays a vital role in the success of our students.
                        Parents are encouraged to get involved in their child’s education, especially in relation to
                        homework. Independent study helps students to develop the research ability, independent thinking
                        and self-motivation required for success in higher education and the workplace.</p>
                    <p>The school has Parents Audit committee, in which Graduate mothers become members and can do audit
                        of any students’ work, on any day at school.</p>
                    <p>Teachers make surprise house visits to monitor students’ attitude and behavior at home and also
                        to understand the child’s family background better.</p>
                </div>

            </div>
        </section>
    </div>

    <div class="page-panel" id="panel-gallery">
        <section class="about-section">
            <div class="container">
                <div class="reveal" style="margin-bottom:36px;">
                    <span class="section-tag">Gallery</span>
                    <h2 class="section-title">Life at Sathya Sai</h2>
                    <div class="section-divider"></div>
                    <p style="color:var(--muted); font-size:.95rem;">Glimpses of our vibrant school community —
                        classrooms,
                        events, performances, and celebrations.</p>
                </div>


                <div class="gallery-grid reveal">
                    <?php foreach ($gallery_images as $idx => $img): ?>
                        <div class="gallery-item" data-idx="<?= $idx ?>">
                            <img src="<?= htmlspecialchars($img['image_path']) ?>"
                                alt="<?= htmlspecialchars($img['caption']) ?>">
                            <div class="overlay"><span><?= htmlspecialchars($img['caption']) ?></span></div>
                        </div>
                    <?php endforeach; ?>
                </div>
        </section>
    </div>

    <div class="page-panel" id="panel-videos">
        <section class="about-section">
            <div class="container">
                <div class="reveal" style="margin-bottom:36px;">
                    <span class="section-tag">Our Videos</span>
                    <h2 class="section-title">School Life &amp; Events</h2>
                    <div class="section-divider"></div>
                    <p style="color:var(--muted); font-size:.95rem;">Watch our school highlights, annual day, sports meet and more.</p>
                </div>

                <?php if (!empty($school_videos)): ?>
                <div class="videos-grid reveal">
                    <?php foreach ($school_videos as $vid):
                        $embed = getEmbedUrl($vid['youtube_url']);
                        if (!$embed) continue;
                    ?>
                    <div class="video-item">
                        <div class="video-wrapper">
                            <iframe
                                src="<?= htmlspecialchars($embed) ?>"
                                title="<?= htmlspecialchars($vid['title']) ?>"
                                loading="lazy" allowfullscreen
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
                <?php else: ?>
                <div style="text-align:center; padding:60px 0; color:var(--muted);">
                    <div style="font-size:3rem; margin-bottom:16px;">🎬</div>
                    <p>Videos coming soon. Check back shortly!</p>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="page-panel" id="panel-career">
        <section class="about-section">
            <div class="container">
                <div class="reveal" style="margin-bottom:36px;">
                    <span class="section-tag">Career With Us</span>
                    <h2 class="section-title">Join the Sai Teaching Family</h2>
                    <div class="section-divider"></div>
                </div>

                <div class="career-hero-block reveal">
                    <div class="text">
                        <h2>Teachers Application</h2>
                        <p>If you want to become a teacher, it's probably because of your experiences in the classroom.
                            Maybe you find inspiration in great teachers or simply your own love of learning.</p>
                        <p>With a career in school teaching, you'll be able to share that love and pass along the skills
                            and knowledge kids need to get a start in life.</p>
                        <a href="#teacher-application-form" class="cta-btn"
                            onclick="document.getElementById('teacher-application-form').scrollIntoView({behavior: 'smooth'}); return false;">🎓
                            Apply to Become a Sai Teacher →</a>
                    </div>
                    <div class="icon-badge">🏫</div>
                </div>

                <!-- Teacher Application Form -->
                <div class="registration-form-wrap reveal" style="margin-top: 56px;" id="teacher-application-form">
                    <h3
                        style="font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--navy); margin-bottom: 24px; text-align: center; padding-bottom: 12px;">
                        Teacher Application Form</h3>
                    <form class="theme-form" method="POST" enctype="multipart/form-data" onsubmit="return validateApplicationForm(this);">
                        <input type="hidden" name="application_type" value="teacher">
                        <input type="hidden" name="school" value="cbse">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="full_name" placeholder="Enter full name" required>
                            </div>
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" name="date_of_birth" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Highest Qualification</label>
                                <input type="text" name="qualification" placeholder="e.g., B.Ed, M.Sc, M.A." required>
                            </div>
                            <div class="form-group">
                                <label>Years of Experience</label>
                                <input type="number" name="years_experience" placeholder="Enter years of experience" min="0"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    title="Please enter only numbers for years of experience." required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Subject(s) Applied For</label>
                                <input type="text" name="subjects" placeholder="e.g., Mathematics, English" required>
                            </div>
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input type="tel" name="phone" placeholder="Enter 10-digit phone number" pattern="[0-9]{10}"
                                    maxlength="10" title="Please enter a valid 10-digit phone number"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" placeholder="Enter email address" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Upload Resume (PDF/DOC)</label>
                            <input type="file" name="resume" accept=".pdf,.doc,.docx" required
                                style="background: transparent; border: 1px dashed rgba(10,31,68,.3); padding: 12px 16px;">
                        </div>
                        <div class="form-group">
                            <label>Residential Address</label>
                            <textarea name="address" rows="3" placeholder="Enter full residential address" required></textarea>
                        </div>
                        <button type="submit" class="cta-btn">Submit Application →</button>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <div class="page-panel" id="panel-admissions">
        <section class="about-section">
            <div class="container">

                <div class="admissions-banner reveal">
                    <h2>🎓 Admissions Open – 2025–2026</h2>
                    <p>Your child's next step towards a bright future. Seats filling fast — register today.</p>
                    <a href="#registration-form" class="cta-btn" style="display:inline-flex;"
                        onclick="document.getElementById('registration-form').scrollIntoView({behavior: 'smooth'}); return false;">Register
                        Now →</a>
                </div>

                <div class="admissions-layout">
                    <!-- Contact Info -->
                    <div class="contact-card reveal-left">
                        <h3>Contact Information</h3>
                        <div class="contact-row">
                            <div class="icon">📍</div>
                            <div class="info">
                                <strong>Address</strong>
                                <span>Sri Sathya Sai Vidya Vihar<br>#101, KCP Road, Thiruvottiyur, Chennai – 600
                                    019.</span>
                            </div>
                        </div>
                        <div class="contact-row">
                            <div class="icon">✉️</div>
                            <div class="info">
                                <strong>Email</strong>
                                <span><a href="mailto:contact@sathyasaischool.in">contact@sathyasaischool.in</a></span>
                            </div>
                        </div>
                        <div class="contact-row">
                            <div class="icon">📞</div>
                            <div class="info">
                                <strong>Phone</strong>
                                <span><a href="tel:9962212161">9962212161</a> / <a
                                        href="tel:9962212171">9962212171</a></span>
                            </div>
                        </div>
                        <div class="contact-row">
                            <div class="icon">🌐</div>
                            <div class="info">
                                <strong>Website</strong>
                                <span><a href="http://www.sathyasaischools.org"
                                        target="_blank">www.sathyasaischools.org</a></span>
                            </div>
                        </div>
                        <div style="margin-top:24px; padding-top:20px; border-top:1px solid rgba(10,31,68,.08);">
                            <strong
                                style="font-size:.78rem; color:var(--muted); text-transform:uppercase; letter-spacing:.1em;">Want
                                to Join?</strong>
                            <div style="display:flex; gap:12px; margin-top:12px; flex-wrap:wrap;">
                                <a href="#registration-form"
                                    onclick="document.getElementById('registration-form').scrollIntoView({behavior: 'smooth'}); return false;"
                                    style="flex:1; min-width:130px; text-align:center; background:linear-gradient(135deg,var(--navy),#0d2960); color:var(--gold-lt); font-size:.82rem; font-weight:700; padding:11px 16px; border-radius:8px;">
                                    👨‍🎓 Be a Sai Student
                                </a>
                                <a href="#teacher-application-form"
                                    onclick="switchTab('career'); setTimeout(() => document.getElementById('teacher-application-form').scrollIntoView({behavior: 'smooth'}), 100); return false;"
                                    style="flex:1; min-width:130px; text-align:center; background:linear-gradient(135deg,var(--gold),#e8a030); color:var(--deep); font-size:.82rem; font-weight:700; padding:11px 16px; border-radius:8px;">
                                    👩‍🏫 Be a Sai Teacher
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Age Table -->
                    <div class="age-table-wrap reveal-right">
                        <h3>Class-wise Age Criteria</h3>
                        <table class="age-table">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Minimum Age (as on 30 June)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Pre-KG</td>
                                    <td><span class="age-badge">2.5 years</span></td>
                                </tr>
                                <tr>
                                    <td>LKG</td>
                                    <td><span class="age-badge">3 years</span></td>
                                </tr>
                                <tr>
                                    <td>UKG</td>
                                    <td><span class="age-badge">4 years</span></td>
                                </tr>
                                <tr>
                                    <td>Class 1 to 5</td>
                                    <td><span class="age-badge">5 to 9 years</span></td>
                                </tr>
                                <tr>
                                    <td>Class 6 to 10</td>
                                    <td><span class="age-badge">10 to 14 years</span></td>
                                </tr>
                            </tbody>
                        </table>
                        <p style="margin-top:18px; font-size:.8rem; color:var(--muted); line-height:1.6;">
                            ⚠️ Age criteria are as per government regulations. Please bring the original birth
                            certificate at the time of registration. Seats are subject to availability.
                        </p>
                    </div>
                </div>

                <!-- Registration Form -->
                <div class="registration-form-wrap reveal" style="margin-top: 56px;" id="registration-form">
                    <h3
                        style="font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--navy); margin-bottom: 24px; text-align: center; padding-bottom: 12px;">
                        Online Registration Form</h3>
                    <form class="theme-form" method="POST" onsubmit="return validateApplicationForm(this);">
                        <input type="hidden" name="application_type" value="admission">
                        <input type="hidden" name="school" value="cbse">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Student's Full Name</label>
                                <input type="text" name="student_name" placeholder="Enter full name" required>
                            </div>
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" name="date_of_birth" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Class Applied For</label>
                                <select name="class_applied" required>
                                    <option value="">Select Class</option>
                                    <option value="Pre-KG">Pre-KG</option>
                                    <option value="LKG">LKG</option>
                                    <option value="UKG">UKG</option>
                                    <option value="1">Class 1</option>
                                    <option value="2">Class 2</option>
                                    <option value="3">Class 3</option>
                                    <option value="4">Class 4</option>
                                    <option value="5">Class 5</option>
                                    <option value="6">Class 6</option>
                                    <option value="7">Class 7</option>
                                    <option value="8">Class 8</option>
                                    <option value="9">Class 9</option>
                                    <option value="10">Class 10</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Parent/Guardian Name</label>
                                <input type="text" name="parent_name" placeholder="Enter parent/guardian name" required>
                            </div>
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input type="tel" name="phone" placeholder="Enter 10-digit phone number" pattern="[0-9]{10}"
                                    maxlength="10" title="Please enter a valid 10-digit phone number"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" placeholder="Enter email address" required>
                        </div>
                        <div class="form-group">
                            <label>Residential Address</label>
                            <textarea name="address" rows="3" placeholder="Enter full residential address" required></textarea>
                        </div>
                        <button type="submit" class="cta-btn">Submit Application →</button>
                    </form>
                </div>

            </div>
        </section>
    </div>

    <!-- FOOTER -->

    <footer>

        <div class="container">

            <div class="footer-grid">

                <div class="footer-col">
                    <h3>Sri Sathya Sai Vidya Vihar</h3>
                    <p>Pre-KG to Grade 10<br>Education with Human Values.</p>
                    <p style="margin-top:12px;">#101, KCP Road,<br>Thiruvottiyur, Chennai – 600 019.</p>
                    <div class="social-row">
                        <a href="https://www.facebook.com/srisathyasaischoolchennai" target="_blank"
                            class="soc-btn">f</a>
                        <a href="https://twitter.com/sathyasaischl" target="_blank" class="soc-btn">𝕏</a>
                        <a href="https://www.linkedin.com/in/srisathyasaischools-thiruvottiyur-4a233a132"
                            target="_blank" class="soc-btn">in</a>
                        <a href="https://www.instagram.com/srisathyasaischools/" target="_blank" class="soc-btn">📷</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <a href="index.php">Home</a>
                    <a href="#" onclick="switchTab('about'); return false;">About CBSE School</a>
                    <a href="#" onclick="switchTab('academics'); return false;">Academics</a>
                    <a href="#" onclick="switchTab('campus'); return false;">Campus &amp; Community</a>
                    <a href="#" onclick="switchTab('gallery'); return false;">Gallery</a>
                    <a href="#" onclick="switchTab('videos'); return false;">Videos</a>
                    <a href="#" onclick="switchTab('career'); return false;">Career With Us</a>
                    <a href="#" onclick="switchTab('admissions'); return false;">New Admissions</a>
                </div>

                <div class="footer-col">
                    <h3>Contact</h3>
                    <a href="tel:04425731075">📞 044 2573 1075 / 4554 3184</a>
                    <a href="tel:7305212695">📱 7305212695 (Admissions)</a>
                    <a href="mailto:contact@sathyasaischool.in">✉ contact@sathyasaischool.in</a>
                    <a href="http://www.sathyasaischool.in" target="_blank">🌐 www.sathyasaischool.in</a>
                    <a href="https://www.google.co.in/maps/@13.1618744,80.3017826,19z" target="_blank" style="margin-top:10px;">📍 View on Google Maps</a>
                </div>

            </div>

            <div class="footer-bottom">
                <p style="margin-bottom: 8px;">📍 Thiruvottiyur, Chennai – 600019 &nbsp;|&nbsp; 📞 9962212161 /
                    9962212171 &nbsp;|&nbsp; ✉ contact@sathyasaischool.in</p>
                <p>All Rights Reserved © 2025 &nbsp;|&nbsp; <a
                        href="http://www.sathyasaischools.org">www.sathyasaischools.org</a>
                    &nbsp;|&nbsp; Designed by <a href="http://www.crb.co.in" target="_blank">CRB Innovative
                        Solutions</a></p>
            </div>

        </div>

    </footer>

    <!-- LIGHTBOX -->
    <div class="lightbox" id="lightbox">
        <button class="lb-close" id="lbClose">✕</button>
        <button class="lb-nav lb-prev" id="lbPrev">&#8249;</button>
        <img src="" alt="" id="lbImg">
        <button class="lb-nav lb-next" id="lbNext">&#8250;</button>
    </div>

    <!-- JAVASCRIPT -->
    <script>
        const panelMeta = {
            about: { title: 'Sri Sathya Sai Vidya Vihar', desc: 'Education with Human Values. A modern CBSE institution dedicated to academic excellence, discipline, spirituality, and holistic development.', crumb: 'CBSE School' },
            academics: { title: 'Academics', desc: 'A holistic, value-based academic programme for every learner.', crumb: 'Academics' },
            campus: { title: 'Campus', desc: 'A vibrant, safe, nurturing campus where every child is known and valued.', crumb: 'Campus' },
            campus: { title: 'Campus &amp; Community', desc: 'A vibrant, safe, nurturing campus where every child is known and valued.', crumb: 'Campus &amp; Community' },
            gallery: { title: 'Gallery', desc: 'Glimpses of life at Sri Sathya Sai Vidya Vihar.', crumb: 'Gallery' },
            videos: { title: 'Our Videos', desc: 'Watch school events, achievements and highlights.', crumb: 'Videos' },
            career: { title: 'Career With Us', desc: 'Join the Sathya Sai family and help shape the leaders of tomorrow.', crumb: 'Career With Us' },
            admissions: { title: 'New Admissions 2025–2026', desc: 'Register your child today for a transformative, value-based education.', crumb: 'New Admissions' }
        };

        // SCROLL EFFECTS
        const navbar = document.getElementById('navbar');
        const floatCta = document.getElementById('floatCta');
        window.addEventListener('scroll', () => {
            if (navbar) navbar.classList.toggle('scrolled', scrollY > 60);
            if (floatCta) floatCta.classList.toggle('show', scrollY > 400);
        });

        function switchTab(panelId) {
            // Update active link
            document.querySelectorAll('#navLinks a[data-nav]').forEach(t => t.classList.remove('active'));
            const navLink = document.querySelector(`#navLinks a[data-nav="${panelId}"]`);
            if (navLink) navLink.classList.add('active');

            // Update active panel
            document.querySelectorAll('.page-panel').forEach(p => p.classList.remove('active'));
            const panel = document.getElementById('panel-' + panelId);
            if (panel) panel.classList.add('active');

            // Update Hero Content
            const meta = panelMeta[panelId];
            if (meta) {
                document.getElementById('heroTitle').innerHTML = meta.title;
                document.getElementById('heroDesc').textContent = meta.desc;
                document.getElementById('heroBreadcrumb').innerHTML = meta.crumb;
            }

            // Scroll behavior
            const heroBottom = document.getElementById('pageHero').offsetTop;
            window.scrollTo({ top: heroBottom - 60, behavior: 'smooth' });

            reObserveReveals();
        }

        // REVEAL OBSERVER
        function reObserveReveals() {
            document.querySelectorAll('.reveal:not(.visible), .reveal-left:not(.visible), .reveal-right:not(.visible)').forEach(el => {
                io.observe(el);
            });
        }
        const io = new IntersectionObserver(entries => {
            entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => io.observe(el));

        // GALLERY LIGHTBOX
        const galleryItems = Array.from(document.querySelectorAll('.gallery-item'));
        const lbEl = document.getElementById('lightbox');
        const lbImg = document.getElementById('lbImg');
        let lbCurrent = 0;

        function openLb(idx) {
            lbCurrent = idx;
            lbImg.src = galleryItems[idx].querySelector('img').src;
            lbEl.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeLb() { lbEl.classList.remove('open'); document.body.style.overflow = ''; }
        function lbNav(dir) { lbCurrent = (lbCurrent + dir + galleryItems.length) % galleryItems.length; lbImg.src = galleryItems[lbCurrent].querySelector('img').src; }

        galleryItems.forEach((item, idx) => item.addEventListener('click', () => openLb(idx)));
        if (document.getElementById('lbClose')) document.getElementById('lbClose').addEventListener('click', closeLb);
        if (document.getElementById('lbPrev')) document.getElementById('lbPrev').addEventListener('click', () => lbNav(-1));
        if (document.getElementById('lbNext')) document.getElementById('lbNext').addEventListener('click', () => lbNav(1));
        if (lbEl) lbEl.addEventListener('click', e => { if (e.target === lbEl) closeLb(); });
        document.addEventListener('keydown', e => {
            if (!lbEl || !lbEl.classList.contains('open')) return;
            if (e.key === 'ArrowLeft') lbNav(-1);
            if (e.key === 'ArrowRight') lbNav(1);
            if (e.key === 'Escape') closeLb();
        });

        // URL HASH ON LOAD
        const hash = location.hash.replace('#', '');
        if (panelMeta[hash]) switchTab(hash);

        // MOBILE MENU
        const hamburger = document.getElementById('hamburger');
        const navLinks = document.getElementById('navLinks');
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('open');
            navLinks.classList.toggle('open');
        });
        navLinks.querySelectorAll('a').forEach(a => {
            a.addEventListener('click', () => {
                hamburger.classList.remove('open');
                navLinks.classList.remove('open');
            });
        });

    </script>
    <script src="application-forms.js"></script>
</body>

</html> const lbImg = document.getElementById('lbImg');
        let lbCurrent = 0;

        function openLb(idx) {
            lbCurrent = idx;
            lbImg.src = galleryItems[idx].querySelector('img').src;
            lbEl.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeLb() { lbEl.classList.remove('open'); document.body.style.overflow = ''; }
        function lbNav(dir) { lbCurrent = (lbCurrent + dir + galleryItems.length) % galleryItems.length; lbImg.src = galleryItems[lbCurrent].querySelector('img').src; }

        galleryItems.forEach((item, idx) => item.addEventListener('click', () => openLb(idx)));
        if (document.getElementById('lbClose')) document.getElementById('lbClose').addEventListener('click', closeLb);
        if (document.getElementById('lbPrev')) document.getElementById('lbPrev').addEventListener('click', () => lbNav(-1));
        if (document.getElementById('lbNext')) document.getElementById('lbNext').addEventListener('click', () => lbNav(1));
        if (lbEl) lbEl.addEventListener('click', e => { if (e.target === lbEl) closeLb(); });
        document.addEventListener('keydown', e => {
            if (!lbEl || !lbEl.classList.contains('open')) return;
            if (e.key === 'ArrowLeft') lbNav(-1);
            if (e.key === 'ArrowRight') lbNav(1);
            if (e.key === 'Escape') closeLb();
        });

        // URL HASH ON LOAD
        const hash = location.hash.replace('#', '');
        if (panelMeta[hash]) switchTab(hash);

        // MOBILE MENU
        const hamburger = document.getElementById('hamburger');
        const navLinks = document.getElementById('navLinks');
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('open');
            navLinks.classList.toggle('open');
        });
        navLinks.querySelectorAll('a').forEach(a => {
            a.addEventListener('click', () => {
                hamburger.classList.remove('open');
                navLinks.classList.remove('open');
            });
        });

    </script>
    <script src="application-forms.js"></script>
</body>

</html>