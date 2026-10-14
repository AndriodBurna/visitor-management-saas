<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('dashboard/role_dashboard', ['roleTitle' => 'Organization workspace']) ?>
<?= $this->endSection() ?>