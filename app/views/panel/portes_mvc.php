<?php
// app/views/panel/portes_mvc.php
// Vista MVC — renderizada por PorteController a través del layout
?>
<section class="section" style="padding: 2rem 0;">
    <div class="container">
        <h2>Portes</h2>

        <?php if (empty($portes)): ?>
            <p>No hay portes registrados.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                    <thead>
                        <tr style="background:#1e293b; color:#fff;">
                            <th style="padding:0.6rem 1rem; text-align:left;">ID</th>
                            <th style="padding:0.6rem 1rem; text-align:left;">Fecha</th>
                            <th style="padding:0.6rem 1rem; text-align:left;">Origen</th>
                            <th style="padding:0.6rem 1rem; text-align:left;">Destino</th>
                            <th style="padding:0.6rem 1rem; text-align:left;">Estado</th>
                            <th style="padding:0.6rem 1rem; text-align:left;">Cliente</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($portes as $p): ?>
                        <tr style="border-bottom:1px solid #e2e8f0;">
                            <td style="padding:0.6rem 1rem;"><?= htmlspecialchars($p['id']) ?></td>
                            <td style="padding:0.6rem 1rem;"><?= htmlspecialchars($p['fecha_programada']) ?></td>
                            <td style="padding:0.6rem 1rem;"><?= htmlspecialchars($p['origen']) ?></td>
                            <td style="padding:0.6rem 1rem;"><?= htmlspecialchars($p['destino']) ?></td>
                            <td style="padding:0.6rem 1rem;"><?= htmlspecialchars($p['estado']) ?></td>
                            <td style="padding:0.6rem 1rem;"><?= htmlspecialchars($p['cliente_nombre'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
