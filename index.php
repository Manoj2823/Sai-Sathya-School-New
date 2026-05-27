<?php
require_once 'db.php';

// Fetch hero slides from DB
$slides_result = mysqli_query($conn, "SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY sort_order ASC");
$slides = [];
while ($row = mysqli_fetch_assoc($slides_result)) {
  $slides[] = $row;
}
// Fallback to static slides if DB is empty
if (empty($slides)) {
  $slides = [
    ['image_path' => 'images/3.jpg', 'tag' => 'Welcome', 'title' => 'A Sai School', 'description' => '&ldquo;The students of today are the citizens of tomorrow who will shape the destiny of a nation. <strong>MY SCHOOLS</strong> have been established to achieve this purpose.&rdquo;<br><small style="color:rgba(255,255,255,0.65);">— Bhagawan Sri Sathya Sai Baba</small>', 'btn_text' => 'Discover Our Schools ↓', 'btn_link' => '#about'],
    ['image_path' => 'images/2.jpg', 'tag' => 'Philosophy', 'title' => 'Educare – Veda of the 21st Century', 'description' => 'Education refers to collection of worldly facts. Educare is to bring out the latent Divinity in Man — Education is for a living; Educare is for life.', 'btn_text' => 'Learn More →', 'btn_link' => '#about'],
    ['image_path' => 'images/5.jpg', 'tag' => 'Values', 'title' => 'Unity of Faiths', 'description' => 'Fostering the belief &ldquo;God is one, Love is God&rdquo; inculcates Unity of Faiths among children — rooted in Sathya, Dharma, Santhi and Prema.', 'btn_text' => 'Our Curriculum →', 'btn_link' => '#curriculum'],
    ['image_path' => 'images/6.jpg', 'tag' => 'Technology', 'title' => 'Sri Sathya Sai Vidya Vahini', 'description' => 'Insightful, Inspiring, Enjoyable and Participative value-based Digital Classrooms — powered by Tata Consultancy Services.', 'btn_text' => 'Explore →', 'btn_link' => '#curriculum'],
    ['image_path' => 'images/9.jpg', 'tag' => 'Innovation', 'title' => 'Synergistic Notebooks', 'description' => 'Enabling students to be creative independent thinkers and writers — used from Grade 1 onwards to spark imagination and ownership of learning.', 'btn_text' => 'Student Stories →', 'btn_link' => '#voices'],
    ['image_path' => 'images/10.jpg', 'tag' => 'Smart School', 'title' => 'Sailens – A Dedicated School App', 'description' => 'The 1st school to introduce RF-enabled ID cards and a dedicated app in Thiruvottiyur. Attendance, GPS tracking, galleries and more — all in one place.', 'btn_text' => 'Get in Touch →', 'btn_link' => '#contact'],
    ['image_path' => 'images/11.jpg', 'tag' => 'Spirit', 'title' => 'We are the Future…', 'description' => 'Sathya Sai Schools, Thiruvottiyur — <em>Globally Focused, Distinctly Indian.</em>', 'btn_text' => 'Register 2025–26 →', 'btn_link' => 'samacheer.php#admissions'],
    ['image_path' => 'images/12.jpg', 'tag' => 'Admissions Open', 'title' => 'School New Admissions 2025–2026', 'description' => 'Your child\'s next step towards a bright future. Registration now open for the new academic year.', 'btn_text' => 'Register Now →', 'btn_link' => 'samacheer.php#admissions'],
  ];
}


require_once 'admin/db.php'; // Unga db connection path correct-ah kuduthukonga

// Fetch Testimonials from DB
$testimonials = [];
try {
    $testi_res = mysqli_query($conn, "SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order ASC, id DESC");
    if ($testi_res) {
        while ($row = mysqli_fetch_assoc($testi_res)) {
            $testimonials[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {
    // Table innum create aagalana ignore pannidalam (fallback static data use aagum)
}

// Fallback to static testimonials if DB is empty
if (empty($testimonials)) {
  $testimonials = [
    ['name' => 'Shiyam M', 'role' => 'Student, VII A2', 'content' => "When the word 'extra' precedes 'ordinary', it gives a whole new meaning. So does the word 'Sai' to a 'Student'. Am blessed to be a SAI STUDENT.", 'avatar_path' => ''],
    ['name' => 'Yuvashree P', 'role' => 'Student, X A3', 'content' => "Sathya Sai is a great place to learn. There are many opportunities for once-in-a-lifetime experiences.", 'avatar_path' => ''],
    ['name' => 'Gopala Krishnan MS', 'role' => 'Student, XII A', 'content' => "Lessons can very often be entertaining as well as educational because of the approach that teachers take.", 'avatar_path' => ''],
    ['name' => 'Elizabeth D', 'role' => 'Student, X A2', 'content' => "Sathya Sai opens a wide variety of doors; I have discovered strengths I never knew I had.", 'avatar_path' => ''],
    ['name' => 'Aathira L J', 'role' => 'Student, VIII A2', 'content' => "On Sunday nights, I never think \"Oh no, it's school tomorrow!\" I love Sathya Sai…", 'avatar_path' => ''],
    ['name' => 'Ms. Pemila Ramesh', 'role' => 'Teacher, Class III', 'content' => "Sathya Sai is a community. Every day presents a new challenge and students and staff work together — it's what makes being a teacher here so rewarding!", 'avatar_path' => ''],
    ['name' => 'Ms. Jayasri S', 'role' => 'Teacher, Class XI', 'content' => "It's the people inside Sathya Sai that make it a special place. Young people who are fun, brilliant, hardworking, and my amazing colleagues who inspire me every day.", 'avatar_path' => ''],
    ['name' => 'Ms. Fathima Shakira Ali', 'role' => 'Teacher, Class IV', 'content' => "This is the school that helped shape my future when I studied here. Now I get to be part of the teaching team shaping the next generation.", 'avatar_path' => ''],
    ['name' => 'Ms. Usharani Bherusingh', 'role' => 'Parent, VIII A2', 'content' => "The school achieves the right balance between working hard and having fun. My daughter thoroughly enjoys school — Sathya Sai is a great place to be, for every child.", 'avatar_path' => ''],
    ['name' => 'Ms. Nandini Krishnamoorthy', 'role' => 'Parent', 'content' => "The behavior of students is exceptional both in and out of classrooms — welcoming, courteous and respectful. This is an outstanding school.", 'avatar_path' => ''],
    ['name' => 'Mr. Ramesh', 'role' => 'Parent', 'content' => "What makes the school special is that as soon as you walk in, you get a burst of positive energy. The school works hand-in-hand with parents.", 'avatar_path' => ''],
    ['name' => 'Sameera Fathima M', 'role' => 'Student, XII A', 'content' => "Our school's high academic standards and family atmosphere nurture a love for learning that brings out the best in all students.", 'avatar_path' => '']
  ];
}

// Home page-kaana contents-a mattum fetch panrom
$content_query = mysqli_query($conn, "SELECT * FROM page_contents WHERE page_name='home'");
$home_contents = [];
if ($content_query) {
  while ($row = mysqli_fetch_assoc($content_query)) {
    if (!isset($row['is_active']) || $row['is_active'] == 1) {
        $home_contents[$row['section_title']] = $row['content_text'];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sri Sathya Sai Schools – Thiruvottiyur, Chennai</title>
  <link rel="icon" type="image/png" href="images/academiya_heading_logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Lato:wght@300;400;700&family=Cinzel:wght@400;600&display=swap"
    rel="stylesheet">
  <style>
    /* ==============================
   CSS VARIABLES & RESET
   ============================== */
    :root {
      --navy: #0a1f44;
      --deep: #071530;
      --gold: #c8932a;
      --gold-lt: #f0c060;
      --cream: #fdf6ec;
      --white: #ffffff;
      --text: #2c2c2c;
      --muted: #6b7280;
      --border: rgba(200, 147, 42, 0.25);
      --shadow: 0 8px 32px rgba(10, 31, 68, 0.18);
      --radius: 12px;
      --trans: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Lato', sans-serif;
      color: var(--text);
      background: var(--cream);
      overflow-x: hidden;
    }

    img {
      max-width: 100%;
      display: block;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    ul {
      list-style: none;
    }

    /* ==============================
   LOADER
   ============================== */
    #loader {
      position: fixed;
      inset: 0;
      z-index: 9999;
      background: var(--deep);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      transition: opacity 0.6s ease, visibility 0.6s ease;
    }

    #loader.hidden {
      opacity: 0;
      visibility: hidden;
    }

    .loader-img {
      max-width: 150px;
      height: auto;
    }

    .loader-bar {
      width: 200px;
      height: 3px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 99px;
      margin-top: 18px;
      overflow: hidden;
    }

    .loader-fill {
      height: 100%;
      width: 0%;
      background: linear-gradient(90deg, var(--gold), var(--gold-lt));
      border-radius: 99px;
      animation: load-fill 1.8s ease forwards;
    }

    @keyframes load-fill {
      to {
        width: 100%;
      }
    }

    /* ==============================
   TOP BAR
   ============================== */
    .top-bar {
      background: var(--deep);
      padding: 6px 0;
      font-size: 0.78rem;
      color: rgba(255, 255, 255, 0.7);
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
      transition: color 0.2s;
    }

    .top-bar a:hover {
      color: #fff;
    }

    /* ==============================
   NAVBAR
   ============================== */
    nav {
      position: sticky;
      top: 0;
      z-index: 1000;
      background: var(--navy);
      box-shadow: 0 2px 20px rgba(0, 0, 0, 0.35);
      transition: background var(--trans);
    }

    nav.scrolled {
      background: rgba(7, 21, 48, 0.97);
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

    .brand-text {
      line-height: 1.1;
    }

    .brand-text span:first-child {
      display: block;
      font-family: 'Playfair Display', serif;
      font-size: 1.05rem;
      color: var(--white);
      font-weight: 600;
    }

    .brand-text span:last-child {
      display: block;
      font-size: 0.7rem;
      color: var(--gold-lt);
      letter-spacing: 0.12em;
      text-transform: uppercase;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .nav-links a {
      color: rgba(255, 255, 255, 0.82);
      font-size: 0.85rem;
      padding: 8px 14px;
      border-radius: 6px;
      transition: all 0.25s;
      position: relative;
      letter-spacing: 0.04em;
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
      transition: width 0.3s;
    }

    .nav-links a:hover {
      color: var(--white);
      background: rgba(255, 255, 255, 0.07);
    }

    .nav-links a:hover::after {
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
      box-shadow: 0 4px 15px rgba(200, 147, 42, 0.45) !important;
      transform: translateY(-1px);
    }

    .nav-cta::after {
      display: none !important;
    }

    /* Hamburger */
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
      transition: all 0.35s;
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

    /* ==============================
   HERO SLIDER
   ============================== */
    .hero-slider {
      position: relative;
      height: 100vh;
      min-height: 560px;
      min-height: 750px;
      overflow: hidden;
      background: var(--deep);
    }

    .slides-track {
      display: flex;
      height: 100%;
      transition: transform 0.85s cubic-bezier(0.77, 0, 0.175, 1);
      will-change: transform;
    }

    .slide {
      min-width: 100%;
      height: 100%;
      position: relative;
      flex-shrink: 0;
    }

    .slide img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(0.55);
    }

    .slide-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(7, 21, 48, 0.72) 0%, rgba(7, 21, 48, 0.2) 60%, transparent 100%);
    }

    .slide-caption {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 60px 6% 80px;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      opacity: 0;
      transform: translateY(30px);
      transition: opacity 0.8s 0.3s ease, transform 0.8s 0.3s ease;
    }

    .slide.active .slide-caption {
      opacity: 1;
      transform: translateY(0);
    }

    .caption-tag {
      background: var(--gold);
      color: var(--deep);
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      padding: 4px 14px;
      border-radius: 99px;
      margin-bottom: 16px;
    }

    .slide-caption h1 {
      font-family: 'Playfair Display', serif;
      font-size: clamp(1.8rem, 4.5vw, 3.4rem);
      color: var(--white);
      line-height: 1.2;
      max-width: 680px;
      margin-bottom: 14px;
      text-shadow: 0 2px 20px rgba(0, 0, 0, 0.4);
      font-family: 'Playfair Display', serif !important;
    }

    .slide-caption p {
      color: rgba(255, 255, 255, 0.88);
      font-size: clamp(0.9rem, 1.5vw, 1.05rem);
      max-width: 560px;
      line-height: 1.7;
      margin-bottom: 24px;
      font-family: 'Lato', sans-serif !important;
    }

    .slide-caption h1 *, .slide-caption p *, .slide-caption small {
      font-family: inherit !important;
    }

    .slide-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(135deg, var(--gold), #e8a030);
      color: var(--deep);
      font-weight: 700;
      font-size: 0.88rem;
      padding: 12px 26px;
      border-radius: 8px;
      letter-spacing: 0.05em;
      transition: all 0.3s;
      box-shadow: 0 4px 20px rgba(200, 147, 42, 0.4);
    }

    .slide-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 28px rgba(200, 147, 42, 0.55);
    }

    /* Slider controls */
    .slider-controls {
      position: absolute;
      bottom: 28px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 10px;
      z-index: 10;
    }

    .dot {
      width: 8px;
      height: 8px;
      border-radius: 99px;
      background: rgba(255, 255, 255, 0.4);
      cursor: pointer;
      transition: all 0.35s;
      border: none;
    }

    .dot.active {
      background: var(--gold-lt);
      width: 26px;
    }

    .slider-arrow {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 46px;
      height: 46px;
      z-index: 10;
      background: rgba(255, 255, 255, 0.12);
      backdrop-filter: blur(6px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #fff;
      border-radius: 50%;
      font-size: 1.2rem;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s;
    }

    .slider-arrow:hover {
      background: var(--gold);
      border-color: var(--gold);
      color: var(--deep);
    }

    .slider-arrow.prev {
      left: 20px;
    }

    .slider-arrow.next {
      right: 20px;
    }

    /* Scroll indicator */
    .scroll-hint {
      position: absolute;
      bottom: 28px;
      right: 30px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      color: rgba(255, 255, 255, 0.55);
      font-size: 0.7rem;
      color: var(--gold-lt);
      font-size: 0.85rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      animation: bounce-scroll 2s ease-in-out infinite;
    }

    .scroll-hint::before {
      content: '';
      width: 1px;
      width: 2px;
      height: 44px;
      background: linear-gradient(to bottom, transparent, rgba(255, 255, 255, 0.5));
      background: linear-gradient(to bottom, transparent, var(--gold-lt));
    }

    @keyframes bounce-scroll {

      0%,
      100% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(8px);
        transform: translateY(15px);
      }
    }

    /* ==============================
   SECTION COMMON
   ============================== */
    section {
      padding: 80px 0;
    }

    .container {
      max-width: 1200px;
      margin: auto;
      padding: 0 20px;
    }

    .section-tag {
      display: inline-block;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      color: var(--gold);
      background: rgba(200, 147, 42, 0.1);
      border: 1px solid rgba(200, 147, 42, 0.3);
      padding: 4px 14px;
      border-radius: 99px;
      margin-bottom: 12px;
    }

    .section-title {
      font-family: 'Playfair Display', serif;
      font-size: clamp(1.6rem, 3vw, 2.4rem);
      color: var(--navy);
      line-height: 1.25;
      margin-bottom: 14px;
    }

    .section-sub {
      font-size: 1rem;
      color: var(--muted);
      line-height: 1.7;
      max-width: 600px;
    }

    .section-divider {
      width: 56px;
      height: 3px;
      background: linear-gradient(90deg, var(--gold), var(--gold-lt));
      border-radius: 2px;
      margin: 14px 0 24px;
    }

    /* Reveal animation */
    .reveal {
      opacity: 0;
      transform: translateY(36px);
      transition: opacity 0.7s ease, transform 0.7s ease;
    }

    .reveal.visible {
      opacity: 1;
      transform: none;
    }

    .reveal-left {
      opacity: 0;
      transform: translateX(-40px);
      transition: opacity 0.7s ease, transform 0.7s ease;
    }

    .reveal-right {
      opacity: 0;
      transform: translateX(40px);
      transition: opacity 0.7s ease, transform 0.7s ease;
    }

    .reveal-left.visible,
    .reveal-right.visible {
      opacity: 1;
      transform: none;
    }

    .delay-1 {
      transition-delay: 0.1s;
    }

    .delay-2 {
      transition-delay: 0.2s;
    }

    .delay-3 {
      transition-delay: 0.3s;
    }

    .delay-4 {
      transition-delay: 0.4s;
    }

    /* ==============================
   STATS STRIP
   ============================== */
    .stats-strip {
      background: linear-gradient(135deg, var(--navy), var(--deep));
      padding: 36px 0;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 24px;
      text-align: center;
    }

    .stat-item {
      padding: 12px 0;
    }

    .stat-num {
      font-family: 'Playfair Display', serif;
      font-size: 2.4rem;
      color: var(--gold-lt);
      font-weight: 700;
      line-height: 1;
    }

    .stat-label {
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.7);
      margin-top: 6px;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }

    .stat-sep {
      width: 1px;
      background: rgba(255, 255, 255, 0.1);
      margin: 0 auto;
      align-self: stretch;
    }

    /* ==============================
   ABOUT / SCHOOL CARDS
   ============================== */
    .about-section {
      background: var(--white);
    }

    .about-header {
      text-align: center;
      margin-bottom: 52px;
    }

    .about-header .section-divider {
      margin: 14px auto 0;
    }

    .schools-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 32px;
    }

    .school-card {
      border-radius: 16px;
      overflow: hidden;
      box-shadow: var(--shadow);
      transition: transform var(--trans), box-shadow var(--trans);
      background: var(--white);
      border: 1px solid rgba(10, 31, 68, 0.07);
    }

    .school-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 20px 50px rgba(10, 31, 68, 0.2);
    }

    .card-img-wrap {
      position: relative;
      height: 320px;
      overflow: hidden;
    }

    .card-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: top;
      object-position: top;
      height: auto;
      transition: transform 0.55s ease;
    }

    .school-card:hover .card-img-wrap img {
      transform: scale(1.06);
    }

    .card-img-badge {
      position: absolute;
      bottom: 16px;
      left: 16px;
      background: var(--navy);
      color: var(--gold-lt);
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      padding: 4px 12px;
      border-radius: 99px;
      text-transform: uppercase;
    }

    .card-body {
      padding: 28px;
    }

    .card-body h2 {
      font-family: 'Playfair Display', serif;
      font-size: 1.3rem;
      color: var(--navy);
      margin-bottom: 4px;
    }

    .card-body h3 {
      font-size: 0.85rem;
      color: var(--gold);
      margin-bottom: 14px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }

    .card-body p {
      font-size: 0.92rem;
      line-height: 1.7;
      color: var(--muted);
      margin-bottom: 20px;
    }

    .card-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: var(--navy);
      font-weight: 700;
      font-size: 0.85rem;
      border-bottom: 2px solid var(--gold);
      padding-bottom: 2px;
      transition: color 0.25s, gap 0.25s;
    }

    .card-link:hover {
      color: var(--gold);
      gap: 14px;
    }

    /* ==============================
   CURRICULUM SECTION
   ============================== */
    .curriculum-section {
      background: linear-gradient(160deg, var(--navy) 0%, #0d2960 100%);
      position: relative;
      overflow: hidden;
    }

    .curriculum-section::before {
      content: '';
      position: absolute;
      top: -120px;
      right: -120px;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(200, 147, 42, 0.12) 0%, transparent 70%);
      border-radius: 50%;
    }

    .curriculum-section .section-title {
      color: var(--white);
    }

    .curriculum-section .section-sub {
      color: rgba(255, 255, 255, 0.65);
    }

    .curriculum-section .section-tag {
      color: var(--gold-lt);
      background: rgba(200, 147, 42, 0.15);
      border-color: rgba(200, 147, 42, 0.35);
    }

    .curriculum-section .section-divider {
      background: linear-gradient(90deg, var(--gold-lt), var(--gold));
    }

    .curri-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      gap: 24px;
      margin-bottom: 40px;
      flex-wrap: wrap;
    }

    /* Tab system */
    .tab-nav {
      display: flex;
      gap: 4px;
      background: rgba(255, 255, 255, 0.07);
      border-radius: 10px;
      padding: 4px;
    }

    .tab-btn {
      padding: 10px 22px;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 600;
      letter-spacing: 0.05em;
      color: rgba(255, 255, 255, 0.55);
      cursor: pointer;
      transition: all 0.3s;
      border: none;
      background: none;
      white-space: nowrap;
      position: relative;
      z-index: 10;
    }


    .tab-btn * {
      pointer-events: none;
    }

    .tab-btn.active {
      background: var(--gold);
      color: var(--deep);
      box-shadow: 0 3px 12px rgba(200, 147, 42, 0.4);
    }

    .tab-btn:hover:not(.active) {
      color: var(--white);
      background: rgba(255, 255, 255, 0.1);
    }

    .tab-panel {
      display: none;
    }

    .tab-panel.active {
      display: block;
    }

    /* Curriculum board items */
    .curri-board {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 48px;
    }

    .board-item {
      text-align: center;
      max-width: 340px;
    }

    .board-item img {
      width: 100px;
      margin: 0 auto 20px;
      filter: brightness(1.1) drop-shadow(0 4px 12px rgba(200, 147, 42, 0.3));
    }

    .board-item h3 {
      font-family: 'Playfair Display', serif;
      font-size: 1.8rem;
      color: var(--gold-lt);
      margin-bottom: 6px;
    }

    .board-item p {
      color: rgba(255, 255, 255, 0.65);
      font-size: 0.92rem;
    }

    /* Beyond curriculum grid */
    .beyond-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
      gap: 16px;
    }

    .beyond-card {
      border-radius: 12px;
      overflow: hidden;
      position: relative;
      aspect-ratio: 1;
      cursor: pointer;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .beyond-card:hover {
      transform: scale(1.03);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .beyond-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .beyond-card figcaption {
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(7, 21, 48, 0.85) 0%, transparent 55%);
      display: flex;
      align-items: flex-end;
      padding: 14px;
      color: var(--white);
      font-weight: 600;
      font-size: 0.85rem;
      letter-spacing: 0.03em;
    }

    /* ==============================
   TESTIMONIALS
   ============================== */
    .testimonials-section {
      background: var(--cream);
    }

    .testi-header {
      text-align: center;
      margin-bottom: 48px;
    }

    .testi-header .section-divider {
      margin: 14px auto 0;
    }

    .testi-slider-wrap {
      position: relative;
      overflow: hidden;
    }

    .testi-track {
      display: flex;
      gap: 24px;
      animation: scroll-testi 40s linear infinite;
      width: max-content;
    }

    .testi-track:hover {
      animation-play-state: paused;
    }

    @keyframes scroll-testi {
      0% {
        transform: translateX(0);
      }

      100% {
        transform: translateX(-50%);
      }
    }

    .testi-card {
      background: var(--white);
      border-radius: 14px;
      padding: 28px;
      width: 320px;
      flex-shrink: 0;
      box-shadow: 0 4px 20px rgba(10, 31, 68, 0.08);
      border: 1px solid rgba(10, 31, 68, 0.06);
      position: relative;
    }

    .testi-card::before {
      content: '\201C';
      font-family: 'Playfair Display', serif;
      font-size: 4rem;
      color: var(--gold);
      position: absolute;
      top: 10px;
      left: 22px;
      line-height: 1;
      opacity: 0.35;
    }

    .testi-card p {
      font-size: 0.9rem;
      line-height: 1.7;
      color: var(--text);
      margin-bottom: 18px;
      padding-top: 20px;
      font-style: italic;
    }

    .testi-meta {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .testi-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--navy), var(--gold));
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-weight: 700;
      font-size: 0.95rem;
      flex-shrink: 0;
    }

    .testi-name {
      font-weight: 700;
      font-size: 0.88rem;
      color: var(--navy);
    }

    .testi-role {
      font-size: 0.78rem;
      color: var(--muted);
    }

    /* Fades at edges */
    .testi-slider-wrap::before,
    .testi-slider-wrap::after {
      content: '';
      position: absolute;
      top: 0;
      bottom: 0;
      width: 80px;
      z-index: 2;
      pointer-events: none;
    }

    .testi-slider-wrap::before {
      left: 0;
      background: linear-gradient(to right, var(--cream), transparent);
    }

    .testi-slider-wrap::after {
      right: 0;
      background: linear-gradient(to left, var(--cream), transparent);
    }

    /* ==============================
   ACTIVITIES / BEYOND
   ============================== */

    /* ==============================
   CONTACT / FOOTER
   ============================== */
    .contact-section {
      background: linear-gradient(160deg, var(--deep), var(--navy));
    }

    .contact-section .section-title {
      color: var(--white);
    }

    .contact-section .section-sub {
      color: rgba(255, 255, 255, 0.62);
    }

    .contact-section .section-tag {
      color: var(--gold-lt);
      background: rgba(200, 147, 42, 0.15);
      border-color: rgba(200, 147, 42, 0.3);
    }

    .contact-grid {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 28px;
      margin-top: 44px;
    }

    .contact-box {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 14px;
      padding: 30px;
      text-align: center;
      transition: all 0.3s;
    }

    .contact-box:hover {
      background: rgba(255, 255, 255, 0.1);
      transform: translateY(-4px);
    }

    .contact-icon {
      width: 52px;
      height: 52px;
      margin: 0 auto 16px;
      background: linear-gradient(135deg, var(--gold), #e8a030);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
    }

    .contact-box h3 {
      color: var(--white);
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 8px;
    }

    .contact-box p {
      color: rgba(255, 255, 255, 0.6);
      font-size: 0.85rem;
      line-height: 1.7;
    }

    .contact-box a {
      color: var(--gold-lt);
    }

    .social-row {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 14px;
      margin-top: 40px;
    }

    .social-row .social-btn {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      display: flex !important;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      color: rgba(255, 255, 255, 0.7);
      line-height: 1;
      transition: all 0.3s;
    }

    .social-row .social-btn:hover {
      background: var(--gold);
      border-color: var(--gold);
      color: var(--deep);
      transform: translateY(-3px);
    }

    /* ==============================
   FOOTER
   ============================== */
    .site-footer {
      background: linear-gradient(160deg, var(--deep), var(--navy));
      padding: 60px 0 0;
      color: #fff;
    }

    .footer-grid {
      display: grid;
      grid-template-columns: 1.2fr 1fr;
      gap: 48px;
      padding-bottom: 40px;
    }

    .footer-col h4 {
      font-family: 'Playfair Display', serif;
      color: var(--gold-lt);
      font-size: 1.1rem;
      margin-bottom: 16px;
      padding-bottom: 10px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .footer-col p,
    .footer-col a {
      font-size: 0.85rem;
      color: rgba(255, 255, 255, 0.6);
      line-height: 1.9;
      display: block;
      transition: color 0.2s;
    }

    .footer-col a:hover {
      color: var(--gold-lt);
    }

    .fb-input {
      width: 100%;
      padding: 10px 12px;
      margin-bottom: 10px;
      border-radius: 6px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      background: rgba(255, 255, 255, 0.05);
      color: #fff;
      font-family: 'Lato', sans-serif;
      font-size: 0.85rem;
    }

    .fb-input::placeholder {
      color: rgba(255, 255, 255, 0.4);
    }

    .fb-input:focus {
      outline: none;
      border-color: var(--gold);
      background: rgba(255, 255, 255, 0.1);
    }

    .fb-btn {
      background: linear-gradient(135deg, var(--gold), #e8a030);
      color: var(--deep);
      font-weight: 700;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.3s;
      font-size: 0.85rem;
    }

    .fb-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(200, 147, 42, 0.4);
    }

    .footer-bottom {
      background: var(--deep);
      border-top: 1px solid rgba(255, 255, 255, 0.07);
      padding: 20px 0;
      text-align: center;
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.4);
    }

    .footer-bottom a {
      color: var(--gold-lt);
    }

    /* ==============================
   REGISTER BANNER
   ============================== */
    .register-banner {
      background: linear-gradient(135deg, var(--gold) 0%, #e8a030 50%, #c8932a 100%);
      padding: 36px 0;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .register-banner::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.07'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .register-banner h2 {
      font-family: 'Playfair Display', serif;
      font-size: clamp(1.4rem, 2.5vw, 2rem);
      color: var(--deep);
      margin-bottom: 8px;
      position: relative;
    }

    .register-banner p {
      color: rgba(7, 21, 48, 0.75);
      font-size: 1rem;
      position: relative;
      margin-bottom: 22px;
    }

    .register-btn {
      display: inline-block;
      background: var(--deep);
      color: var(--gold-lt);
      font-weight: 700;
      font-size: 0.92rem;
      letter-spacing: 0.08em;
      padding: 13px 32px;
      border-radius: 8px;
      transition: all 0.3s;
      position: relative;
      box-shadow: 0 4px 20px rgba(7, 21, 48, 0.3);
    }

    .register-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 30px rgba(7, 21, 48, 0.45);
    }

    /* ==============================
   FLOATING FLOATING CTA
   ============================== */
    .floating-cta {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 888;
      background: linear-gradient(135deg, var(--gold), #e8a030);
      color: var(--deep);
      font-weight: 700;
      font-size: 0.8rem;
      padding: 11px 18px;
      border-radius: 50px;
      box-shadow: 0 6px 24px rgba(200, 147, 42, 0.5);
      transition: all 0.3s;
      letter-spacing: 0.05em;
      display: flex;
      align-items: center;
      gap: 6px;
      opacity: 0;
      pointer-events: none;
    }

    .floating-cta.show {
      opacity: 1;
      pointer-events: all;
    }

    .floating-cta:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 32px rgba(200, 147, 42, 0.65);
    }

    /* ==============================
   RESPONSIVE
   ============================== */
    @media (max-width: 900px) {
      .schools-grid {
        grid-template-columns: 1fr;
      }

      .contact-grid {
        grid-template-columns: 1fr 1fr;
      }

      .curri-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .beyond-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      }

      .footer-grid {
        grid-template-columns: 1fr 1fr;
      }
    }

    @media (max-width: 768px) {
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
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        z-index: 999;
      }

      .nav-links.open a {
        padding: 10px 14px;
        border-radius: 8px;
      }

      .nav-links.open li:last-child {
        margin-top: 16px;
      }

      .hamburger {
        display: flex;
      }

      .hero-slider {
        height: 80vh;
        height: 100vh;
        min-height: 600px;
      }

      .slide-caption {
        padding: 30px 20px 70px;
      }

      .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 16px;
      }

      section {
        padding: 56px 0;
      }

      .contact-grid {
        grid-template-columns: 1fr;
      }

      .testi-card {
        width: 280px;
      }

      .slider-arrow {
        width: 36px;
        height: 36px;
        font-size: 1rem;
      }

      .footer-grid {
        grid-template-columns: 1fr;
      }
    }

    /* 📱 Responsive Fixes for Mobile & Tablet (up to 1024px) */
    @media (max-width: 1200px) {

      .hero-slider {
        width: 100% !important;
        height: auto !important;
        min-height: auto !important;
        padding-top: 60px !important;
        padding-bottom: 20px !important;
        overflow: hidden !important;
      }

      /* 2. Track setup */
      .slides-track {
        display: flex !important;
        width: 100% !important;
      }

      .slide {
        width: 100% !important;
        min-width: 100% !important;
        flex-shrink: 0 !important;
      }

      /* 3. The exact Image Fix for iPads & Phones */
      .slide img {
        width: 100% !important;
        height: 60vh !important;
        /* Tablet/Mobile render height */
        object-fit: cover !important;
        object-position: center !important;
      }

      /* 4. Text and Caption adjustments */
      .slide-caption {
        width: 90% !important;
        left: 5% !important;
        bottom: 5% !important;
        text-align: center !important;
      }

      .slide-caption h1 {
        font-size: 1.8rem !important;
        margin-bottom: 8px !important;
      }

      .slide-caption p {
        display: none !important;
        /* Mobile/Tablet-la hide panrathu nallathu */
      }

      /* 5. Controls (Dots & Arrows) - Centered Fix */
      .slider-controls {
        position: relative !important;
        bottom: auto !important;
        margin-top: 20px !important;

        /* Ithu thaan center panrathukaana pudhu code 👇 */
        width: 100% !important;
        display: flex !important;
        justify-content: center !important;
        left: 0 !important;
        transform: none !important;
      }
    }
  </style>
</head>

<body>

  <!-- LOADER -->
  <div id="loader">
    <img src="images/loading_logo.gif" alt="Loading..." class="loader-img">
    <div class="loader-bar">
      <div class="loader-fill"></div>
    </div>
  </div>

  <!-- FLOATING CTA -->
  <a href="samacheer.php#admissions" class="floating-cta" id="floatCta">
    🎓 Apply Now
  </a>

  <!-- HOME PAGE AUDIO -->
  <audio id="homeAudio" controls autoplay loop
    style="position: fixed; bottom: 24px; left: 24px; z-index: 888; height: 44px; max-width: 220px; box-shadow: 0 6px 24px rgba(10, 31, 68, 0.3); border-radius: 30px;">
    <source src="images/tere_vandan_se_karte_hain.mp3" type="audio/mpeg">
    Your browser does not support the audio element.
  </audio>

  <!-- HERO SLIDER -->
  <section class="hero-slider" id="home">
    <div class="slides-track" id="slidesTrack">

      <?php foreach ($slides as $i => $slide): ?>
        <div class="slide<?= $i === 0 ? ' active' : '' ?>">
          <img src="<?= htmlspecialchars($slide['image_path']) ?>" alt="<?= htmlspecialchars($slide['title']) ?>">
          <div class="slide-overlay"></div>
          <div class="slide-caption">
            <span class="caption-tag"><?= htmlspecialchars($slide['tag']) ?></span>
            <h1><?= htmlspecialchars($slide['title']) ?></h1>
            <p><?= $slide['description'] ?></p>
            <a href="<?= htmlspecialchars($slide['btn_link']) ?>"
              class="slide-btn"><?= htmlspecialchars($slide['btn_text']) ?></a>
          </div>
        </div>
      <?php endforeach; ?>

    </div>

    <!-- Arrows -->
    <button class="slider-arrow prev" id="sliderPrev" aria-label="Previous">&#8249;</button>
    <button class="slider-arrow next" id="sliderNext" aria-label="Next">&#8250;</button>

    <!-- Dots -->
    <div class="slider-controls" id="sliderDots"></div>

    <!-- Scroll hint -->
    <div class="scroll-hint">Scroll</div>
  </section>

  <!-- STATS STRIP -->
  <div class="stats-strip">
    <div class="container">
      <div class="stats-grid reveal">
        <div class="stat-item">
          <div class="stat-num" data-target="37">0</div>
          <div class="stat-label">Years of Excellence</div>
        </div>
        <div class="stat-item">
          <div class="stat-num" data-target="100">0</div>
          <div class="stat-label" style="white-space:nowrap;">% Board Pass Rate</div>
        </div>
        <div class="stat-item">
          <div class="stat-num" data-target="2">0</div>
          <div class="stat-label">Campuses</div>
        </div>
        <div class="stat-item">
          <div class="stat-num" data-target="10">0</div>
          <div class="stat-label">Co-curricular Activities</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ABOUT -->
  <section class="about-section" id="about">
    <div class="container">
      <div class="about-header reveal">
        <span class="section-tag"><?= $home_contents['about_tag'] ?? 'Our Schools' ?></span>
        <h2 class="section-title"><?= $home_contents['about_title'] ?? 'Two Campuses, One Vision' ?></h2>
        <div class="section-divider"></div>
        <p class="section-sub" style="margin:0 auto;">
          <?= $home_contents['about_subtitle'] ?? 'Both schools are inspired, initiated and lovingly nurtured by Bhagawan Sri Sathya Sai Baba — dedicated to holistic, value-based education since 1987.' ?>
        </p>
      </div>

      <div class="schools-grid">
        <div class="school-card reveal-left delay-1">
          <div class="card-img-wrap">
            <img src="images/samacheer_school_img.png" alt="Sri Sathya Sai Vidyalaya">
            <span class="card-img-badge"><?= $home_contents['samacheer_badge'] ?? 'Est. 1987' ?></span>
          </div>
          <div class="card-body">
            <h2><?= $home_contents['samacheer_name'] ?? 'Sri Sathya Sai Vidyalaya' ?></h2>
            <h3><?= $home_contents['samacheer_board'] ?? 'Samacheer School · Tamil Nadu State Board' ?></h3>
            <p>
              <?= $home_contents['samacheer_desc'] ?? 'Embarked on its mission of moulding young minds on 24th April 1987, the Vidyalaya has been producing State Ranks and 100% pass percentage in board exams continuously, pursuing excellence under His guidance and loving care.' ?>
            </p>
            <a href="samacheer.php" class="card-link" target="_blank">
              Visit our site <span>→</span>
            </a>
          </div>
        </div>

        <div class="school-card reveal-right delay-2">
          <div class="card-img-wrap">
            <img src="images/cbse_school_img.png" alt="Sri Sathya Sai Vidya Vihar">
            <span class="card-img-badge"><?= $home_contents['cbse_badge'] ?? 'Est. 2016' ?></span>
          </div>
          <div class="card-body">
            <h2><?= $home_contents['cbse_name'] ?? 'Sri Sathya Sai Vidya Vihar' ?></h2>
            <h3><?= $home_contents['cbse_board'] ?? 'School · PreP to Grade 5' ?></h3>
            <p>
              <?= $home_contents['cbse_desc'] ?? 'Launched in June 2016 in a sprawling green campus of more than 1 acre, with state-of-the-art infrastructure, spacious classrooms, modern labs, and high-end sports facilities — an elite value-based school in Thiruvottiyur.' ?>
            </p>
            <a href="cbse.php" class="card-link" target="_blank">
              Visit our site <span>→</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CURRICULUM -->
  <section class="curriculum-section" id="curriculum">
    <div class="container">
      <div class="curri-header">
        <div class="reveal">
          <span class="section-tag"><?= $home_contents['curriculum_tag'] ?? 'What We Offer' ?></span>
          <h2 class="section-title"><?= $home_contents['curriculum_title'] ?? 'The Curriculum &amp; Beyond' ?></h2>
          <div class="section-divider"></div>
          <p class="section-sub">
            <?= $home_contents['curriculum_desc'] ?? 'Sathya Sai provides a secure, challenging yet supportive learning environment in which students gain confidence both in curriculum and beyond — through a host of enriching activities.' ?>
          </p>
        </div>
        <div class="tab-nav reveal delay-2" id="tabNav">
          <button class="tab-btn active" data-tab="curriculum">📚 The Curriculum</button>
          <button class="tab-btn" data-tab="beyond">🎨 Beyond Curriculum</button>
        </div>
      </div>

      <div class="tab-panel active" id="tab-curriculum">
        <div class="curri-board reveal">
          <div class="board-item">
            <img src="images/academics_logo2.png" alt="Samacheer">
            <h3>SAMACHEER</h3>
            <p>Tamil Nadu State Board — A rigorous, holistic curriculum designed to develop well-rounded students with
              strong foundations in all disciplines.</p>
          </div>
        </div>
      </div>

      <div class="tab-panel" id="tab-beyond">
        <div class="beyond-grid reveal">
          <figure class="beyond-card">
            <img src="images/beyond_logo9.jpg" alt="Art & Craft">
            <figcaption>Sameep Art &amp; Craft</figcaption>
          </figure>
          <figure class="beyond-card">
            <img src="images/beyond_logo2.jpg" alt="Meditation">
            <figcaption>Meditation</figcaption>
          </figure>
          <figure class="beyond-card">
            <img src="images/beyond_logo1.jpg" alt="Classical Dance">
            <figcaption>Classical Dance</figcaption>
          </figure>
          <figure class="beyond-card">
            <img src="images/beyond_logo3.jpg" alt="Music">
            <figcaption>Music</figcaption>
          </figure>
          <figure class="beyond-card">
            <img src="images/beyond_logo4.jpg" alt="Karate">
            <figcaption>Karate</figcaption>
          </figure>
          <figure class="beyond-card">
            <img src="images/beyond_logo6.jpg" alt="Cricket">
            <figcaption>Sai Cricket Academy</figcaption>
          </figure>
          <figure class="beyond-card">
            <img src="images/beyond_logo7.jpg" alt="Abacus">
            <figcaption>Abacus</figcaption>
          </figure>
          <figure class="beyond-card">
            <img src="images/beyond_logo8.jpg" alt="Yoga">
            <figcaption>Yoga</figcaption>
          </figure>
          <figure class="beyond-card">
            <img src="images/beyond_logo10.jpg" alt="Chess">
            <figcaption>Chess</figcaption>
          </figure>
          <figure class="beyond-card">
            <img src="images/beyond_logo11.jpg" alt="Musical Instruments">
            <figcaption>Musical Instruments</figcaption>
          </figure>
        </div>
      </div>
    </div>
  </section>

  <!-- REGISTER BANNER -->
  <div class="register-banner">
    <div class="container">
      <h2><?= $home_contents['banner_title'] ?? 'Admissions Open – Academic Year 2025–2026' ?></h2>
      <p><?= $home_contents['banner_desc'] ?? "Your child's next step towards a bright future starts here." ?></p>
      <a href="samacheer.php#admissions"
        class="register-btn"><?= $home_contents['banner_btn'] ?? 'Register Now →' ?></a>
    </div>
  </div>

  <!-- VOICES / TESTIMONIALS -->
  <section class="testimonials-section" id="voices">
    <div class="container">
      <div class="testi-header reveal">
        <span class="section-tag"><?= $home_contents['testi_tag'] ?? 'Heart to Heart' ?></span>
        <h2 class="section-title"><?= $home_contents['testi_title'] ?? 'Why We Love Sathya Sai?' ?></h2>
        <div class="section-divider"></div>
        <p class="section-sub" style="margin:0 auto;">
          <?= $home_contents['testi_desc'] ?? 'Our open door policy is the foundation upon which we build excellent relationships. The person who benefits most from this positive collaboration is none other than the student.' ?>
        </p>
      </div>
    </div>

    <div class="testi-slider-wrap reveal">
      <div class="testi-track" id="testiTrack">
        <?php
        // Outputting the loop twice ensures the continuous CSS scroll works smoothly
        for ($i = 0; $i < 2; $i++):
          foreach ($testimonials as $testi):
            $initial = strtoupper(substr($testi['name'], 0, 1));
        ?>
          <div class="testi-card">
            <p><?= nl2br(htmlspecialchars($testi['content'])) ?></p>
            <div class="testi-meta">
              <div class="testi-avatar">
                <?php if (!empty($testi['avatar_path'])): ?>
                  <img src="<?= htmlspecialchars($testi['avatar_path']) ?>" alt="Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                  <?= $initial ?>
                <?php endif; ?>
              </div>
              <div>
                <div class="testi-name"><?= htmlspecialchars($testi['name']) ?></div>
                <div class="testi-role"><?= htmlspecialchars($testi['role']) ?></div>
              </div>
            </div>
          </div>
        <?php
          endforeach;
        endfor;
        ?>
      </div>
    </div>
  </section>

  <!-- CONTACT SECTION -->
  <section class="contact-section" id="contact">
    <div class="container">
      <div class="reveal" style="text-align:center; margin-bottom:8px;">
        <span class="section-tag"><?= $home_contents['contact_tag'] ?? 'Get In Touch' ?></span>
        <h2 class="section-title"><?= $home_contents['contact_title'] ?? "Together We're Stronger" ?></h2>
        <div class="section-divider" style="margin:14px auto;"></div>
        <p class="section-sub" style="margin:0 auto; color:rgba(255,255,255,0.6);">
          <?= $home_contents['contact_desc'] ?? "Share our values, set your compass and start your journey with us. Together let's build exciting opportunities for our children." ?>
        </p>
      </div>

      <div class="contact-grid">
        <div class="contact-box reveal delay-1">
          <div class="contact-icon">📍</div>
          <h3><?= $home_contents['contact1_title'] ?? 'Sri Sathya Sai Vidyalaya' ?></h3>
          <p>
            <?= $home_contents['contact1_details'] ?? '#3, Nadabai Garden<br>Thiruvottiyur, Chennai – 600 019<br><a href="tel:04425731075">044 2573 1075 / 4554 3184</a>' ?>
          </p>
        </div>
        <div class="contact-box reveal delay-2">
          <div class="contact-icon">🏫</div>
          <h3><?= $home_contents['contact2_title'] ?? 'Sri Sathya Sai Vidya Vihar' ?></h3>
          <p>
            <?= $home_contents['contact2_details'] ?? '#101, KCP Road<br>Thiruvottiyur, Chennai – 600 019<br><a href="tel:+919444080024">+91 94440 80024</a>' ?>
          </p>
        </div>
        <div class="contact-box reveal delay-3">
          <div class="contact-icon">✉️</div>
          <h3><?= $home_contents['contact3_title'] ?? 'Email &amp; Social' ?></h3>
          <p>
            <?= $home_contents['contact3_details'] ?? '<a href="mailto:contact@sathyasaischool.in">contact@sathyasaischool.in</a><br><a href="mailto:contact@sathyasaischools.org">contact@sathyasaischools.org</a>' ?>
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        
        <!-- Column 1: Contact Details -->
        <div class="footer-col">
          <h4>Contact Details</h4>
          <p><strong>Sri Sathya Sai Schools — Thiruvottiyur</strong><br>
          Education with Human Values.</p>
          <p style="margin-top:12px; display:flex; align-items:flex-start; gap:8px;">
            <span>📍</span>
            <span>#3 Nadabai Garden, Thiruvottiyur, Chennai – 600 019</span>
          </p>
          <p style="display:flex; align-items:flex-start; gap:8px;">
            <span>📞</span>
            <span><a href="tel:04425731075" style="display:inline;">044 2573 1075</a> / <a href="tel:7305212695" style="display:inline;">7305212695</a></span>
          </p>
          <p style="display:flex; align-items:flex-start; gap:8px;">
            <span>✉</span>
            <span><a href="mailto:contact@sathyasaischool.in" style="display:inline;">contact@sathyasaischool.in</a></span>
          </p>

          <div class="social-row" style="justify-content: flex-start; margin-top: 20px;">
            <a href="https://www.facebook.com/srisathyasaischoolchennai" target="_blank" class="social-btn" title="Facebook">f</a>
            <a href="https://twitter.com/sathyasaischl" target="_blank" class="social-btn" title="Twitter">𝕏</a>
            <a href="https://www.linkedin.com/in/srisathyasaischools-thiruvottiyur-4a233a132" target="_blank" class="social-btn" title="LinkedIn">in</a>
            <a href="https://www.instagram.com/srisathyasaischools/" target="_blank" class="social-btn" title="Instagram">📷</a>
          </div>
        </div>

        <!-- Column 2: Feedback Form -->
        <div class="footer-col">
          <h4>Share Your Feedback</h4>
          <form id="feedbackForm">
            <input type="text" name="fb_name" placeholder="Your Name" required class="fb-input">
            <input type="text" name="fb_role" placeholder="Role (e.g., Parent, Student, Alumnus)" required class="fb-input">
            <textarea name="fb_content" placeholder="Your Feedback or Testimonial..." required class="fb-input" rows="3"></textarea>
            <button type="submit" class="fb-btn">Submit Feedback</button>
            <div id="fb-msg" style="margin-top: 8px; font-size: 0.85rem; display: none;"></div>
          </form>
        </div>

      </div>
    </div>
    <div class="footer-bottom">
      <div class="container">
        <p style="margin-bottom: 8px;">📍 #3 Nadabai Garden, Thiruvottiyur, Chennai – 600 019 &nbsp;|&nbsp; 📞 <a href="tel:04425731075">044 2573 1075</a> &nbsp;|&nbsp; ✉ <a href="mailto:contact@sathyasaischool.in">contact@sathyasaischool.in</a></p>
        <p>All Rights Reserved © 2025 &nbsp;|&nbsp; <a href="http://www.sathyasaischools.org">www.sathyasaischools.org</a> &nbsp;|&nbsp; Designed by <a href="http://www.crb.co.in" target="_blank">CRB Innovative Solutions</a></p>
      </div>
    </div>
  </footer>

  <!-- JAVASCRIPT -->
  <script>
    // ── LOADER ──────────────────────────────────────────────────────────────
    window.addEventListener('load', () => {
      setTimeout(() => {
        document.getElementById('loader').classList.add('hidden');
      }, 3000);
    });

    // ── NAV SCROLL ───────────────────────────────────────────────────────────
    const floatCta = document.getElementById('floatCta');
    window.addEventListener('scroll', () => {
      floatCta.classList.toggle('show', window.scrollY > 500);
    });

    // ── HERO SLIDER ──────────────────────────────────────────────────────────
    const track = document.getElementById('slidesTrack');
    const slides = track.querySelectorAll('.slide');
    const dotsEl = document.getElementById('sliderDots');
    let current = 0, timer = null, isAnimating = false;
    const N = slides.length;

      slides.forEach((_, i) => {
      const d = document.createElement('button');
      d.className = 'dot' + (i === 0 ? ' active' : '');
      d.setAttribute('aria-label', 'Slide ' + (i + 1));
      d.addEventListener('click', () => goTo(i));
      dotsEl.appendChild(d);
    });

    function goTo(idx) {
      if (isAnimating || idx === current) return;
      isAnimating = true;
      slides[current].classList.remove('active');
      dotsEl.children[current].classList.remove('active');
      current = (idx + N) % N;
      track.style.transform = `translateX(-${current * 100}%)`;
      slides[current].classList.add('active');
      dotsEl.children[current].classList.add('active');
      setTimeout(() => { isAnimating = false; }, 900);
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function startAuto() { timer = setInterval(next, 2000); }
    function stopAuto() { clearInterval(timer); }

    document.getElementById('sliderNext').addEventListener('click', () => { stopAuto(); next(); startAuto(); });
    document.getElementById('sliderPrev').addEventListener('click', () => { stopAuto(); prev(); startAuto(); });

    // Touch/swipe
    let touchX = 0;
    track.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; stopAuto(); }, { passive: true });
    track.addEventListener('touchend', e => {
      const dx = e.changedTouches[0].clientX - touchX;
      if (Math.abs(dx) > 40) dx < 0 ? next() : prev();
      startAuto();
    }, { passive: true });

    // Pause on hover
    track.addEventListener('mouseenter', stopAuto);
    track.addEventListener('mouseleave', startAuto);

    startAuto();

    // ── TABS ─────────────────────────────────────────────────────────────────
    document.getElementById('tabNav').addEventListener('click', e => {
      const btn = e.target.closest('.tab-btn');
      if (!btn) return;
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      const panel = document.getElementById('tab-' + btn.dataset.tab);
      if (panel) panel.classList.add('active');
    });

    // ── REVEAL ON SCROLL ─────────────────────────────────────────────────────
    const revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });
    revealEls.forEach(el => io.observe(el));

    // ── COUNTER ANIMATION ────────────────────────────────────────────────────
    const counters = document.querySelectorAll('.stat-num[data-target]');
    const cio = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (!e.isIntersecting) return;
        const el = e.target;
        const end = +el.dataset.target;
        const suffix = el.nextSibling?.textContent?.includes('%') ? '%' : (end === 100 ? '%' : '+');
        let start = 0;
        const step = () => {
          start += Math.ceil(end / 40);
          if (start >= end) { el.textContent = end + (end === 100 ? '%' : '+'); return; }
          el.textContent = start + (end === 100 ? '%' : '+');
          requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
        cio.unobserve(el);
      });
    }, { threshold: 0.5 });
    counters.forEach(c => cio.observe(c));

    // ── SMOOTH SCROLL ────────────────────────────────────────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(a => {
      a.addEventListener('click', e => {
        const target = document.querySelector(a.getAttribute('href'));
        if (!target) return;
        e.preventDefault();
        window.scrollTo({ top: target.offsetTop, behavior: 'smooth' });
      });
    });

    // ── FEEDBACK FORM SUBMISSION ─────────────────────────────────────────────
    const feedbackForm = document.getElementById('feedbackForm');
    if (feedbackForm) {
      feedbackForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const msgEl = document.getElementById('fb-msg');
        const btn = feedbackForm.querySelector('button[type="submit"]');
        const formData = new FormData(feedbackForm);
        
        msgEl.style.display = 'block';
        msgEl.style.color = '#f0c060';
        msgEl.textContent = 'Submitting...';
        btn.disabled = true;
        
        try {
          const response = await fetch('submit_feedback.php', { method: 'POST', body: formData });
          const result = await response.json();
          if (result.success) {
            msgEl.style.color = '#bbf7d0';
            msgEl.textContent = result.message;
            feedbackForm.reset();
          } else {
            msgEl.style.color = '#fecaca';
            msgEl.textContent = result.message;
          }
        } catch (error) {
          msgEl.style.color = '#fecaca';
          msgEl.textContent = 'Something went wrong. Please try again.';
        }
        btn.disabled = false;
      });
    }
  </script>
</body>

</html>