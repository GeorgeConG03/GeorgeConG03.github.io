<?php
// Archivo: perfil-foros.php
// Sección de foros para incluir en perfil.php
require_once 'includes/foros_functions.php';

// Obtener foros del usuario
$foros_usuario = obtenerForosUsuario($user_data['id_usuario']);
?>

<div class="perfil-section">
    <h2 class="section-title">Foros Unidos</h2>
    
    <?php if (!empty($foros_usuario)): ?>
    <div class="foros-grid">
        <?php foreach ($foros_usuario as $foro): ?>
        <div class="foro-card">
            <div class="foro-card-header">
                <img src="<?php echo htmlspecialchars($foro['imagen_portada'] ?: 'foros/portadas/default-foro.jpg'); ?>" 
                     alt="Portada del foro" class="foro-card-img">
                <div class="foro-card-overlay">
                    <?php if ($foro['rol'] == 'admin'): ?>
                        <span class="foro-role-badge admin">👑 ADMIN</span>
                    <?php elseif ($foro['rol'] == 'moderador'): ?>
                        <span class="foro-role-badge mod">🔨 MOD</span>
                    <?php else: ?>
                        <span class="foro-role-badge member">👤 MIEMBRO</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="foro-card-content">
                <h3 class="foro-card-title">
                    <a href="<?php echo htmlspecialchars($foro['archivo_php']); ?>">
                        <?php echo htmlspecialchars($foro['titulo']); ?>
                    </a>
                </h3>
                
                <p class="foro-card-description">
                    <?php echo htmlspecialchars(substr($foro['descripcion'] ?: 'Sin descripción', 0, 100)) . (strlen($foro['descripcion'] ?: '') > 100 ? '...' : ''); ?>
                </p>
                
                <div class="foro-card-stats">
                    <span class="stat">👥 <?php echo $foro['total_miembros']; ?></span>
                    <span class="stat">💬 <?php echo $foro['total_posts']; ?></span>
                </div>
                
                <div class="foro-card-meta">
                    <small>Creado por: 
                        <a href="perfil.php?id=<?php echo $foro['id_creador']; ?>">
                            <?php echo htmlspecialchars($foro['nombre_creador']); ?>
                        </a>
                    </small>
                    <small>Unido: <?php echo date('d/m/Y', strtotime($foro['fecha_union'])); ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="no-foros">
        <p>No te has unido a ningún foro aún.</p>
        <a href="crear-foro.php" class="btn btn-primary">Crear Foro</a>
        <a href="index.php" class="btn btn-secondary">Explorar Foros</a>
    </div>
    <?php endif; ?>
</div>

<style>
.foros-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.foro-card {
    background-color: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    border: 2px solid #0066cc;
    transition: transform 0.3s ease;
}

.foro-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
}

.foro-card-header {
    position: relative;
    height: 150px;
    overflow: hidden;
}

.foro-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.foro-card-overlay {
    position: absolute;
    top: 10px;
    right: 10px;
}

.foro-role-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-family: "Bangers", cursive;
    font-weight: bold;
    text-transform: uppercase;
}

.foro-role-badge.admin {
    background-color: #ff3366;
    color: white;
}

.foro-role-badge.mod {
    background-color: #28a745;
    color: white;
}

.foro-role-badge.member {
    background-color: #0066cc;
    color: white;
}

.foro-card-content {
    padding: 1.5rem;
}

.foro-card-title {
    font-family: "Bangers", cursive;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.foro-card-title a {
    color: #0066cc;
    text-decoration: none;
}

.foro-card-title a:hover {
    text-decoration: underline;
}

.foro-card-description {
    color: #666;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.foro-card-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.stat {
    font-family: "Bangers", cursive;
    color: #333;
    font-size: 1rem;
}

.foro-card-meta {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.foro-card-meta small {
    color: #888;
    font-size: 0.85rem;
}

.foro-card-meta a {
    color: #0066cc;
    text-decoration: none;
}

.foro-card-meta a:hover {
    text-decoration: underline;
}

.no-foros {
    text-align: center;
    padding: 3rem;
    background-color: #f8f9fa;
    border-radius: 10px;
    border: 2px solid #ddd;
}

.no-foros p {
    margin-bottom: 1.5rem;
    color: #666;
    font-size: 1.1rem;
}

.no-foros .btn {
    margin: 0 0.5rem;
}

@media (max-width: 768px) {
    .foros-grid {
        grid-template-columns: 1fr;
    }
    
    .no-foros .btn {
        display: block;
        margin: 0.5rem 0;
        width: 100%;
    }
}
</style>
