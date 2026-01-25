<!-- Hero Section -->
<section class="hero">
    <div class="container hero-content">
        <div class="hero-text">
            <h2>Logística rápida, segura y <span class="highlight">eficiente</span></h2>
            <p>Conectamos su negocio con el destino. Soluciones de transporte adaptadas a sus necesidades en Málaga y toda la península.</p>
            <div class="hero-buttons">
                <a href="#contacto" class="btn btn-primary btn-lg">Solicitar Presupuesto</a>
                <a href="#servicios" class="btn btn-secondary btn-lg">Explorar Servicios</a>
            </div>
        </div>
        <div class="hero-visual">
            <!-- Decorative element or image placeholder -->
            <div class="glass-card">
                <i class="fas fa-truck-moving"></i>
                <span>Envíos en tiempo real</span>
            </div>
        </div>
    </div>
</section>

<!-- Features / Services -->
<section id="servicios" class="section services">
    <div class="container">
        <div class="section-header">
            <h3>Nuestros Servicios</h3>
            <p>Calidad y compromiso en cada kilómetro.</p>
        </div>
        <div class="services-grid">
            <article class="service-card">
                <div class="icon-box">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h4>Transporte Urgente</h4>
                <p>Entregas en el mismo día para envíos críticos dentro de la provincia de Málaga.</p>
            </article>
            <article class="service-card">
                <div class="icon-box">
                    <i class="fas fa-boxes"></i>
                </div>
                <h4>Logística de Almacén</h4>
                <p>Gestión de stock y almacenamiento seguro para su mercancía.</p>
            </article>
            <article class="service-card">
                <div class="icon-box">
                    <i class="fas fa-route"></i>
                </div>
                <h4>Rutas Optimizadas</h4>
                <p>Tecnología GPS propia para garantizar la ruta más eficiente y económica.</p>
            </article>
        </div>
    </div>
</section>

<!-- Fleet Gallery -->
<section id="flota" class="section bg-light">
    <div class="container">
        <div class="section-header">
            <h3>Nuestra Flota</h3>
            <p>Vehículos modernos y adaptados para cualquier carga.</p>
        </div>
        <div class="services-grid">
            <div class="service-card" style="padding: 1rem;">
                <img src="<?php echo URL_ROOT; ?>/assets/img/truck-1.png" alt="Camión Villalobos" style="border-radius: 8px; width: 100%; height: 200px; object-fit: cover;">
                <h4 style="margin-top: 1rem;">Camiones Rígidos</h4>
            </div>
            <div class="service-card" style="padding: 1rem;">
                <img src="<?php echo URL_ROOT; ?>/assets/img/truck-2.jpg" alt="Furgoneta Villalobos" style="border-radius: 8px; width: 100%; height: 200px; object-fit: cover;">
                <h4 style="margin-top: 1rem;">Furgonetas Express</h4>
            </div>
            <div class="service-card" style="padding: 1rem;">
                <img src="<?php echo URL_ROOT; ?>/assets/img/truck-3.jpg" alt="Transporte Especial" style="border-radius: 8px; width: 100%; height: 200px; object-fit: cover;">
                <h4 style="margin-top: 1rem;">Transporte Especial</h4>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section id="contacto" class="section cta" style="background: white; color: var(--dark);">
    <div class="container">
        <div class="section-header">
            <h3>Contacto y Presupuestos</h3>
            <p>Cuéntenos qué necesita y le responderemos en menos de 24h.</p>
        </div>
        <div class="contact-grid">
            <div class="contact-info-box">
                <div class="contact-info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>Visítenos</h4>
                        <p>Polígono Guadalhorce, s/n<br>29004, Málaga</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h4>Llámenos</h4>
                        <p>630 518 441<br>625 038 039</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h4>Escríbanos</h4>
                        <p>info@villaloboslogistica.com</p>
                    </div>
                </div>
            </div>

            <form class="contact-form" action="<?php echo URL_ROOT; ?>/contact/send" method="POST">
                <div class="form-group">
                    <label for="name">Nombre Completo</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Ej. Juan Pérez" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="juan@ejemplo.com" required>
                </div>
                <div class="form-group">
                    <label for="message">Detalles del Transporte</label>
                    <textarea id="message" name="message" class="form-control" placeholder="Origen, destino, tipo de carga..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%">Enviar Solicitud</button>
            </form>
        </div>
    </div>
</section>