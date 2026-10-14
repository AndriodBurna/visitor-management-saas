<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$visitor = $visitor ?? [];
$value = static function (string $field) use ($visitor): string {
    return old($field) ?? (string) ($visitor[$field] ?? '');
};
?>
<div class="breadcrumb" style="margin-bottom: var(--space-3);"><a href="<?= site_url('visitors') ?>">All Visitors</a><span class="breadcrumb__sep">/</span><a href="<?= site_url('visitors/' . ($visitor['id'] ?? '')) ?>"><?= esc($visitor['full_name'] ?? 'Visitor') ?></a><span class="breadcrumb__sep">/</span><span>Edit</span></div>
<div class="section-title"><div><h2>Edit visitor</h2><p class="text-muted">Keep identity and contact information accurate for future visits.</p></div></div>
<form action="<?= site_url('visitors/' . ($visitor['id'] ?? '') . '/update') ?>" method="post">
  <?= csrf_field() ?>
  <div class="card" style="max-width: 800px;"><div class="card-header"><div><h2>Visitor information</h2><p>Organization assignment and visit history remain unchanged.</p></div></div><div class="card-body"><div class="form-grid">
    <div class="form-group form-group--full"><label class="form-label" for="full_name">Full name</label><input class="form-control <?= session('errors.full_name') ? 'is-invalid' : '' ?>" id="full_name" name="full_name" value="<?= esc($value('full_name')) ?>" required autofocus><?php if (session('errors.full_name')): ?><span class="form-error"><?= esc(session('errors.full_name')) ?></span><?php endif; ?></div>
    <div class="form-group"><label class="form-label" for="phone">Phone <span class="optional">(optional)</span></label><input class="form-control" type="tel" id="phone" name="phone" value="<?= esc($value('phone')) ?>"></div>
    <div class="form-group"><label class="form-label" for="email">Email <span class="optional">(optional)</span></label><input class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= esc($value('email')) ?>"><?php if (session('errors.email')): ?><span class="form-error"><?= esc(session('errors.email')) ?></span><?php endif; ?></div>
    <div class="form-group form-group--full"><label class="form-label" for="company">Company <span class="optional">(optional)</span></label><input class="form-control" id="company" name="company" value="<?= esc($value('company')) ?>"></div>
    <div class="form-group"><label class="form-label" for="identification_type">Identification type <span class="optional">(optional)</span></label><input class="form-control" id="identification_type" name="identification_type" value="<?= esc($value('identification_type')) ?>"></div>
    <div class="form-group"><label class="form-label" for="identification_number">Identification number <span class="optional">(optional)</span></label><input class="form-control font-mono" id="identification_number" name="identification_number" value="<?= esc($value('identification_number')) ?>"></div>
  </div></div><div class="card-footer"><a href="<?= site_url('visitors/' . ($visitor['id'] ?? '')) ?>" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-success">Save visitor</button></div></div>
</form>
<?= $this->endSection() ?>