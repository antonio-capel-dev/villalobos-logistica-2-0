<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- SEO Meta Tags -->
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <meta name="description" content="Villalobos Logística: Soluciones de transporte y logística integral en Málaga. Gestión de flotas, envíos rápidos y seguros.">
    <meta name="author" content="Antonio Capel">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo URL_ROOT; ?>">
    <meta property="og:title" content="Villalobos Logística 2.0 - Transporte Profesional en Málaga">
    <meta property="og:description" content="Expertos en logística y transporte en la Costa del Sol.">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/styles.css">
</head>

</head>

<body>
    <header class="main-header">
        <!-- Top Bar moved inside Header -->
        <div class="top-bar">
            <div class="container">
                <a href="tel:630518441"><i class="fas fa-phone-alt"></i> 630 518 441</a>
                <a href="mailto:info@villaloboslogistica.com"><i class="fas fa-envelope"></i> info@villaloboslogistica.com</a>
            </div>
        </div>

        <div class="container header-container">
            <div class="logo">
                <a href="<?php echo URL_ROOT; ?>">
                    <img src="<?php echo URL_ROOT; ?>/assets/img/logo.png" alt="Villalobos Logística">
                    <div class="logo-text">
                        <span class="logo-title">Villalobos</span>
                        <span class="logo-subtitle">Logística</span>
                    </div>
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo URL_ROOT; ?>">Inicio</a></li>
                    <li><a href="#servicios">Servicios</a></li>
                    <li><a href="#contacto" class="btn btn-primary">Pedir Presupuesto</a></li>
                    <li><a href="<?php echo URL_ROOT; ?>/admin" class="btn btn-outline">Acceso Privado</a></li>
                </ul>
            </nav>
            <button class="mobile-menu-toggle" aria-label="Abrir menú">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <main>
        <?php
        if (isset($viewContent) && file_exists($viewContent)) {
            require_once $viewContent;
        } else {
            echo "<p>Contenido no disponible.</p>";
        }
        ?>
    </main>

    <footer class="main-footer">
        <div class="container footer-grid">
            <div class="footer-col">
                <h3>Villalobos Logística</h3>
                <p>Tu socio de confianza en transporte y logística en Málaga.</p>
            </div>
            <div class="footer-col">
                <h4>Enlaces</h4>
                <ul>
                    <li><a href="#">Aviso Legal</a></li>
                    <li><a href="#">Política de Privacidad</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contacto</h4>
                <p><i class="fas fa-map-marker-alt"></i> Málaga, España</p>
                <p><i class="fas fa-envelope"></i> info@villalobos.com</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Villalobos Logística. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="<?php echo URL_ROOT; ?>/assets/js/main.js"></script>
</body>

</html>