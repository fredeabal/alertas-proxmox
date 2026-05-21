<!DOCTYPE html>
<html lang="es" dir="ltr" data-bs-theme="dark" data-color-theme="Blue_Theme" data-layout="vertical" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">

<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/images/logos/favicon.png') ?>" />

  <!-- Core Css -->
  <link rel="stylesheet" href="<?= base_url('assets/css/styles.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>" />
  
  <!-- FontAwesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

  <!-- PWA Support -->
  <link rel="manifest" href="<?= base_url('manifest.json') ?>" />
  <meta name="theme-color" content="#5d87ff" />
  <link rel="apple-touch-icon" href="<?= base_url('assets/images/logos/logo-app.png?v=2') ?>" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

  <title><?= $title ?? 'Proxmox Alert' ?></title>

  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('<?= base_url('sw.js') ?>')
          .then(reg => console.log('Service Worker registrado'))
          .catch(err => console.log('Error al registrar Service Worker', err));
      });
    }
  </script>
</head>

<body>
  <div id="main-wrapper">
    <?= view('template/sidebar') ?>
    <?= view('template/topbar', ['show_sidebar' => true]) ?>
