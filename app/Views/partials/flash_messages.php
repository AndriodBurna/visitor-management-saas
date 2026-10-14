<?php
// Reads standard CodeIgniter 4 flashdata keys set by your controllers:
//   session()->setFlashdata('success', 'Visitor checked in.');
//   session()->setFlashdata('error', 'Something went wrong.');
//   session()->setFlashdata('warning', '...');
//   session()->setFlashdata('info', '...');
// Falls back to nothing if the session service isn't available in a given context.
$flashTypes = [
    'success' => ['class' => 'alert-success', 'label' => 'Success'],
    'error'   => ['class' => 'alert-danger',  'label' => 'Error'],
    'warning' => ['class' => 'alert-warning', 'label' => 'Warning'],
    'info'    => ['class' => 'alert-info',    'label' => 'Note'],
];
?>
<?php foreach ($flashTypes as $key => $meta): ?>
  <?php $message = session()->getFlashdata($key) ?? null; ?>
  <?php if ($message): ?>
    <div class="alert <?= $meta['class'] ?>">
      <span class="alert__icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </span>
      <div class="alert__body">
        <div class="alert__title"><?= esc($meta['label']) ?></div>
        <div><?= esc($message) ?></div>
      </div>
      <button type="button" class="alert__close" data-alert-close aria-label="Dismiss">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
  <?php endif; ?>
<?php endforeach; ?>

<?php if (session()->getFlashdata('errors')): ?>
  <div class="alert alert-danger">
    <span class="alert__icon">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </span>
    <div class="alert__body">
      <div class="alert__title">Please correct the following</div>
      <ul>
        <?php foreach (session()->getFlashdata('errors') as $error): ?>
          <li><?= esc($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
<?php endif; ?>
