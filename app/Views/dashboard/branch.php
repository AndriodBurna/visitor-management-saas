<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('dashboard/role_dashboard', ['roleTitle' => 'Branch workspace']) ?>
<?= $this->endSection() ?>