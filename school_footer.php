<?php
// Default fallback values just in case variables are not defined
$footer_school_name = $footer_school_name ?? 'Sri Sathya Sai Schools';
$footer_grades = $footer_grades ?? '';
$footer_address = $footer_address ?? '';
$footer_bottom_text = $footer_bottom_text ?? '';
?>
<style>
/* ── FOOTER STYLES ────────────────────────────────────────────── */
.site-footer {
    background: linear-gradient(160deg, var(--deep), var(--navy));
    padding: 48px 0 0;
    color: #fff;
}
.footer-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 32px;
    padding-bottom: 40px;
}
.footer-col h4 {
    font-family: 'Playfair Display', serif;
    color: var(--gold-lt);
    font-size: 1.1rem;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, .1);
}
.footer-col p,
.footer-col a {
    font-size: .85rem;
    color: rgba(255, 255, 255, .6);
    line-height: 1.9;
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
    background: var(--deep);
    border-top: 1px solid rgba(255, 255, 255, .07);
    padding: 18px 0;
    text-align: center;
    font-size: .78rem;
    color: rgba(255, 255, 255, .38);
}
.footer-bottom a {
    color: var(--gold-lt);
}
@media(max-width:900px) {
    .footer-grid { grid-template-columns: 1fr 1fr; }
}
@media(max-width:768px) {
    .footer-grid { grid-template-columns: 1fr; }
}
</style>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h4><?= htmlspecialchars($footer_school_name) ?></h4>
                <p><?= $footer_grades ?><br>Education with Human Values.</p>
                <p style="margin-top:12px;"><?= $footer_address ?></p>
                <div class="social-row">
                    <a href="https://www.facebook.com/srisathyasaischoolchennai" target="_blank" class="soc-btn">f</a>
                    <a href="https://twitter.com/sathyasaischl" target="_blank" class="soc-btn">𝕏</a>
                    <a href="https://www.linkedin.com/in/srisathyasaischools-thiruvottiyur-4a233a132" target="_blank" class="soc-btn">in</a>
                    <a href="https://www.instagram.com/srisathyasaischools/" target="_blank" class="soc-btn">📷</a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <a href="index.php">Home</a>
                <a href="#" onclick="switchTab('about');return false;">About School</a>
                <a href="#" onclick="switchTab('academics');return false;">Academics</a>
                <a href="#" onclick="switchTab('campus');return false;">Campus &amp; Community</a>
                <a href="#" onclick="switchTab('gallery');return false;">Gallery</a>
                <a href="#" onclick="switchTab('videos');return false;">Videos</a>
                <a href="#" onclick="switchTab('career');return false;">Career With Us</a>
                <a href="#" onclick="switchTab('admissions');return false;">New Admissions</a>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <a href="tel:04425731075">📞 044 2573 1075 / 4554 3184</a>
                <a href="tel:7305212695">📱 7305212695 (Admissions)</a>
                <a href="mailto:contact@sathyasaischool.in">✉ contact@sathyasaischool.in</a>
                <a href="http://www.sathyasaischool.in" target="_blank">🌐 www.sathyasaischool.in</a>
                <a href="https://www.google.co.in/maps/@13.1618744,80.3017826,19z" target="_blank" style="margin-top:10px;">📍 View on Google Maps</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p style="margin-bottom: 8px;"><?= $footer_bottom_text ?></p>
            <p>All Rights Reserved © 2025 &nbsp;|&nbsp; <a href="http://www.sathyasaischool.in">www.sathyasaischool.in</a> &nbsp;|&nbsp; Designed by <a href="http://www.crb.co.in" target="_blank">CRB Innovative Solutions</a></p>
        </div>
    </div>
</footer>
