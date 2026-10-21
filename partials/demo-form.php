<?php /** Demo request form (local validation only; sends nothing until an endpoint and consent text are approved). */ ?>
<div class="form-card">
  <form id="demo-form" novalidate>
    <h3><?= e($form_title ?? 'Book your free demo') ?></h3>
    <p><?= e($form_sub ?? 'Tell us about your store and we’ll call you.') ?></p>
    <div class="form-grid">
      <div class="fld"><label for="f-name">Your name</label><input id="f-name" name="name" autocomplete="name" required><span class="err">Please enter your name.</span></div>
      <div class="fld"><label for="f-phone">Mobile number</label><input id="f-phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel" placeholder="10-digit mobile" required pattern="[6-9][0-9]{9}"><span class="err">Please enter a 10-digit mobile number.</span></div>
      <div class="fld"><label for="f-store">Store or hospital name</label><input id="f-store" name="store" autocomplete="organization" required><span class="err">Please enter your store name.</span></div>
      <div class="fld"><label for="f-city">City</label><input id="f-city" name="city" autocomplete="address-level2" required><span class="err">Please enter your city.</span></div>
      <div class="fld full"><label for="f-current">What do you use today?</label>
        <select id="f-current" name="current">
          <option value="">Select one</option><option>Desktop pharmacy software</option><option>General billing app</option><option>Excel or paper registers</option><option>Hospital software</option><option>Opening a new store</option>
        </select></div>
      <div class="fld full"><label for="f-want">I’d like to</label>
        <select id="f-want" name="want">
          <option value="demo">Book a free demo</option><option value="quote">Get Enterprise pricing for my stores</option><option value="checklist">Get the buyer’s checklist</option>
        </select></div>
    </div>
    <button class="btn btn-primary" type="submit"><span id="submit-label">Book my free demo</span> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></button>
    <p class="form-small" data-confirm>We’ll only use your details to contact you about this request. See our <a href="<?= url('privacy') ?>">privacy policy</a>.</p>
  </form>
  <div class="form-success" id="form-success" hidden tabindex="-1">
    <img class="hb" src="<?= asset('img/heartbeat.gif') ?>" width="120" height="115" alt="" loading="lazy" decoding="async"><div class="sol-ic hb-still"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg></div>
    <h3>Thank you, we’ll call you soon</h3>
    <p>Prototype only: nothing was sent.</p>
  </div>
</div>
