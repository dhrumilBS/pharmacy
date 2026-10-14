<?php
/**
 * Closing CTA band (unless $hide_cta), site footer, mobile bar and scripts.
 * Optional: $extra_js (array of asset paths).
 */
$footer_link = function (string $key, string $label) { return '<li><a href="' . e(url($key)) . '">' . e($label) . '</a></li>'; };
?>
<?php if (empty($hide_cta)): ?>
<section class="cta-band" aria-labelledby="cta-band-title">
  <div class="container cta-band-row">
    <div>
      <h2 id="cta-band-title">See it working with medicines from your own shelf</h2>
      <p>Book a short demo, or start free on the Starter plan, no payment needed.</p>
    </div>
    <div class="cta-band-btns">
      <a class="btn btn-light" href="<?= url('demo') ?>" data-cta="demo">Book a free demo <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
      <a class="btn btn-ghost-light" href="<?= e(SITE['app_signup']) ?>">Start free</a>
    </div>
  </div>
</section>
<?php endif; ?>

<footer class="ft" id="contact-footer">
  <div class="container">
    <div class="ft-top">
      <div class="ft-brand">
        <a class="brand" href="<?= url('home') ?>" aria-label="Home"><?= logo() ?></a>
        <p>Billing, expiry, stock, purchase and GST software for medical stores, pharmacy chains and hospital pharmacies.</p>
        <ul class="ft-contact">
          <li><a href="tel:<?= SITE['sales_tel'] ?>"><svg class="icon" aria-hidden="true"><use href="#i-phone"/></svg>Sales <?= e(SITE['sales_phone']) ?></a></li>
          <li><a href="tel:<?= SITE['support_tel'] ?>"><svg class="icon" aria-hidden="true"><use href="#i-headset"/></svg>Support <?= e(SITE['support_phone']) ?></a></li>
          <?php if (SITE['email']): ?><li><a href="mailto:<?= e(SITE['email']) ?>"><svg class="icon" aria-hidden="true"><use href="#i-mail"/></svg><?= e(SITE['email']) ?></a></li><?php endif; ?>
        </ul>
        <?php if (SITE['social']): ?>
        <ul class="ft-social" aria-label="Social media">
          <?php $icons = ['LinkedIn'=>'i-linkedin','YouTube'=>'i-youtube','Instagram'=>'i-instagram','Facebook'=>'i-facebook','X'=>'i-xsocial'];
          foreach (SITE['social'] as $name => $href): ?>
            <li><a href="<?= e($href) ?>" aria-label="<?= e($name) ?>" rel="noopener"><svg class="icon" aria-hidden="true"><use href="#<?= $icons[$name] ?>"/></svg></a></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
      <nav aria-labelledby="ft-h-features"><h3 id="ft-h-features">Features</h3><ul class="links">
        <?php foreach (NAV_FEATURES as $items) foreach ($items as $it) echo $footer_link($it[0], $it[1]); ?>
      </ul></nav>
      <nav aria-labelledby="ft-h-solutions"><h3 id="ft-h-solutions">Solutions</h3><ul class="links">
        <?php foreach (NAV_SOLUTIONS as $it) echo $footer_link($it[0], $it[1]); ?>
        <?= $footer_link('pricing', 'Pricing') ?><?= $footer_link('switch', 'Switch from your current software') ?>
      </ul></nav>
      <nav aria-labelledby="ft-h-resources"><h3 id="ft-h-resources">Resources</h3><ul class="links">
        <?php foreach (NAV_RESOURCES as $it) if ($it[0] !== 'support') echo $footer_link($it[0], $it[1]); ?>
        <li><a href="<?= e(SITE['help_url']) ?>">Help centre</a></li>
      </ul></nav>
      <nav aria-labelledby="ft-h-company"><h3 id="ft-h-company">Company</h3><ul class="links">
        <?= $footer_link('about', 'About') ?><?= $footer_link('contact', 'Contact') ?><?= $footer_link('support', 'Support') ?><?= $footer_link('demo', 'Book a demo') ?>
        <li><a href="<?= e(SITE['app_login']) ?>">Log in</a></li>
        <li><a href="<?= e(SITE['app_signup']) ?>">Sign up free</a></li>
      </ul></nav>
    </div>
    <div class="ft-bottom">
      <span>© <span data-year>2026</span><?= BRAND ? ' ' . e(BRAND) : '' ?>. Draft website, not yet published.</span>
      <nav aria-label="Legal">
        <a href="<?= url('privacy') ?>">Privacy</a><a href="<?= url('terms') ?>">Terms</a><a href="<?= url('refund') ?>">Refund policy</a><a href="<?= url('data-security') ?>">Data &amp; security</a><a href="<?= url('cookies') ?>">Cookies</a>
      </nav>
    </div>
    <?php if (BRAND): ?><p class="ft-word" aria-hidden="true"><?= e(BRAND) ?></p><?php endif; ?>
  </div>
</footer>

<div class="mobile-bar" aria-label="Quick actions">
  <a class="btn btn-secondary" href="tel:<?= SITE['sales_tel'] ?>"><svg class="icon" aria-hidden="true"><use href="#i-phone"/></svg>Call</a>
  <a class="btn btn-secondary" href="<?= e(SITE['app_signup']) ?>">Start free</a>
  <a class="btn btn-primary" href="<?= url('demo') ?>" data-cta="demo">Book demo</a>
</div>

<script src="<?= asset('js/site.js') ?>" defer></script>
<?php foreach (($extra_js ?? []) as $js): ?><script src="<?= asset($js) ?>" defer></script><?php endforeach; ?>
</body>
</html>
