<nav class="navbar">
    <div class="logo">
        <img src="/FmFl/assets/rec/fullmoon.png" alt="Full Moon Logo">
        <a href="/FmFl/index.php" class="logo-text">FULL MOON, FULL LIFE</a>
    </div>
    
    <!-- Buscador -->
    <div class="search-container">
        <form action="/FmFl/buscar.php" method="get" class="search-form">
            <input type="text" name="q" placeholder="Buscar usuarios, foros o reseñas..." class="search-input">
            <button type="submit" class="search-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </button>
        </form>
    </div>
    
    <div class="nav-links">
        <a href="/FmFl/foros.php" class="nav-link">FOROS</a>
        <a href="/FmFl/series.php" class="nav-link">SERIES</a>
        <a href="/FmFl/pelis.php" class="nav-link">PELÍCULAS</a>
        <a href="/FmFl/libros.php" class="nav-link">LIBROS</a>
        
        <?php if (is_logged_in()): ?>
            <!-- Menú de usuario con avatar -->
            <div class="user-menu">
                <div class="avatar-container" id="avatar-toggle">
                    <img src="<?php echo get_user_avatar(); ?>" alt="Avatar" class="avatar-img">
                </div>
                <div class="user-dropdown" id="user-dropdown">
                    <a href="/FmFl/perfil.php?id=<?php echo $_SESSION['user_id']; ?>" class="user-dropdown-link">Perfil</a>
                    <a href="/FmFl/cerrar_sesion.php" class="user-dropdown-link">Cerrar sesión</a>
                </div>
            </div>
        <?php else: ?>
            <a href="/FmFl/ini_sec.php" class="nav-link">INICIAR SESIÓN</a>
            <a href="/FmFl/registro.php" class="nav-link">REGISTRARSE</a>
        <?php endif; ?>
        
        <!-- Botón hamburguesa para móvil -->
        <button class="hamburger-btn" id="hamburger-btn">
            <div class="hamburger-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>
    </div>
</nav>

<!-- Menú móvil -->
<div class="mobile-menu" id="mobile-menu">
    <!-- Buscador móvil -->
    <div class="mobile-search-container">
        <form action="/FmFl/buscar.php" method="get" class="mobile-search-form">
            <input type="text" name="q" placeholder="Buscar..." class="mobile-search-input">
            <button type="submit" class="mobile-search-button">Buscar</button>
        </form>
    </div>
    
    <a href="/FmFl/foros.php" class="mobile-nav-link">FOROS</a>
    <a href="/FmFl/series.php" class="mobile-nav-link">SERIES</a>
    <a href="/FmFl/pelis.php" class="mobile-nav-link">PELÍCULAS</a>
    <a href="/FmFl/libros.php" class="mobile-nav-link">LIBROS</a>
    <?php if (is_logged_in()): ?>
        <a href="/FmFl/perfil.php?id=<?php echo $_SESSION['user_id']; ?>" class="mobile-nav-link">PERFIL</a>
        <a href="/FmFl/cerrar_sesion_directo.php" class="mobile-nav-link">CERRAR SESIÓN</a>
    <?php else: ?>
        <a href="/FmFl/ini_sec.php" class="mobile-nav-link">INICIAR SESIÓN</a>
        <a href="/FmFl/registro.php" class="mobile-nav-link">REGISTRARSE</a>
    <?php endif; ?>
</div>

<!-- Overlay para el menú móvil -->
<div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>

<style>
/* Estilos para el navbar */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
    background-color: rgba(0, 0, 0, 0.7);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
    position: relative;
    z-index: 1000;
}

.logo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo img {
    height: 40px;
    width: auto;
}

.logo-text {
    font-family: "Bangers", cursive;
    font-size: 1.8rem;
    color: white;
    text-decoration: none;
    text-shadow: 3px 3px 0 #ff3366;
}

/* Estilos para el buscador */
.search-container {
    flex: 1;
    max-width: 400px;
    margin: 0 20px;
}

.search-form {
    display: flex;
    position: relative;
}

.search-input {
    width: 100%;
    padding: 8px 40px 8px 15px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    background-color: rgba(0, 0, 0, 0.3);
    color: white;
    font-size: 14px;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #ff3366;
    background-color: rgba(0, 0, 0, 0.5);
    box-shadow: 0 0 10px rgba(255, 51, 102, 0.3);
}

.search-input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.search-button {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.search-icon {
    width: 16px;
    height: 16px;
}

/* Estilos para los enlaces de navegación */
.nav-links {
    display: flex;
    gap: 1.5rem;
    align-items: center;
}

.nav-link {
    color: white;
    text-decoration: none;
    font-family: "Bangers", cursive;
    font-size: 1.3rem;
    text-shadow: 2px 2px 0 #0066cc;
    transition: transform 0.2s;
    padding: 0.5rem 1rem;
    position: relative;
}

.nav-link:hover {
    transform: scale(1.1);
}

.nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 3px;
    background-color: #ff3366;
    transition: width 0.3s ease-in-out;
}

.nav-link:hover::after {
    width: 100%;
}

/* Estilos para el avatar y menú de usuario */
.user-menu {
    position: relative;
    margin-left: 1rem;
}

.avatar-container {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid #ff3366;
    transition: transform 0.2s;
}

.avatar-container:hover {
    transform: scale(1.1);
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background-color: rgba(0, 0, 0, 0.8);
    border-radius: 5px;
    padding: 0.5rem 0;
    min-width: 150px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
    display: none;
    z-index: 1001;
    margin-top: 0.5rem;
}

.user-dropdown.active {
    display: block;
}

.user-dropdown-link {
    display: block;
    color: white;
    text-decoration: none;
    padding: 0.7rem 1.5rem;
    font-size: 1.1rem;
    position: relative;
    overflow: hidden;
}

.user-dropdown-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 51, 102, 0.2);
    transition: left 0.3s ease-in-out;
    z-index: -1;
}

.user-dropdown-link:hover::before {
    left: 0;
}

/* Estilos para el buscador en móvil */
.mobile-search-container {
    padding: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.mobile-search-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.mobile-search-input {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    background-color: rgba(0, 0, 0, 0.3);
    color: white;
    font-size: 14px;
}

.mobile-search-button {
    padding: 8px;
    background-color: #ff3366;
    color: white;
    border: none;
    border-radius: 20px;
    font-family: "Bangers", cursive;
    cursor: pointer;
}

/* Ocultar el botón hamburguesa por defecto */
.hamburger-btn {
    display: none;
    background: none;
    border: none;
    color: white;
    font-size: 1.8rem;
    cursor: pointer;
    z-index: 1001;
    padding: 0.5rem;
}

.hamburger-icon {
    display: block;
    width: 30px;
    height: 20px;
    position: relative;
}

.hamburger-icon span {
    display: block;
    position: absolute;
    height: 3px;
    width: 100%;
    background: white;
    border-radius: 3px;
    opacity: 1;
    left: 0;
    transform: rotate(0deg);
    transition: .25s ease-in-out;
}

.hamburger-icon span:nth-child(1) {
    top: 0px;
}

.hamburger-icon span:nth-child(2) {
    top: 8px;
}

.hamburger-icon span:nth-child(3) {
    top: 16px;
}

.hamburger-btn.active .hamburger-icon span:nth-child(1) {
    top: 8px;
    transform: rotate(135deg);
}

.hamburger-btn.active .hamburger-icon span:nth-child(2) {
    opacity: 0;
    left: -60px;
}

.hamburger-btn.active .hamburger-icon span:nth-child(3) {
    top: 8px;
    transform: rotate(-135deg);
}

/* Menú móvil */
.mobile-menu {
    position: fixed;
    top: 0;
    right: -100%;
    width: 80%;
    max-width: 300px;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.9);
    z-index: 1000;
    transition: right 0.3s ease-in-out;
    padding: 5rem 1rem 2rem;
    overflow-y: auto;
}

.mobile-menu.active {
    right: 0;
}

.mobile-nav-link {
    display: block;
    color: white;
    text-decoration: none;
    padding: 1rem 1.5rem;
    font-size: 1.3rem;
    margin-bottom: 0.5rem;
    border-radius: 5px;
    position: relative;
    overflow: hidden;
    font-family: "Bangers", cursive;
}

.mobile-nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 51, 102, 0.2);
    transition: left 0.3s ease-in-out;
    z-index: -1;
}

.mobile-nav-link:hover::before {
    left: 0;
}

.mobile-menu-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
    display: none;
}

.mobile-menu-overlay.active {
    display: block;
}

/* Ajustes responsivos */
@media (max-width: 992px) {
    .search-container {
        max-width: 300px;
    }
}

@media (max-width: 768px) {
    .search-container {
        display: none;
    }
    
    .navbar {
        justify-content: space-between;
    }
    
    /* Mostrar el botón hamburguesa en pantallas pequeñas */
    .hamburger-btn {
        display: block;
    }
    
    /* Ocultar los enlaces de navegación en pantallas pequeñas */
    .nav-links .nav-link {
        display: none;
    }
    
    /* Mantener visible el avatar en pantallas pequeñas */
    .user-menu {
        display: flex;
        align-items: center;
    }
    
    /* Ajustar el menú de usuario en móvil */
    .user-dropdown {
        right: -50px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle del menú de usuario
    const avatarToggle = document.getElementById('avatar-toggle');
    const userDropdown = document.getElementById('user-dropdown');
    
    if (avatarToggle) {
        avatarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });
    }
    
    // Cerrar el menú de usuario al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (userDropdown && userDropdown.classList.contains('active')) {
            if (!userDropdown.contains(e.target) && !avatarToggle.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        }
    });
    
    // Toggle del menú móvil
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
    
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', function() {
            hamburgerBtn.classList.toggle('active');
            mobileMenu.classList.toggle('active');
            mobileMenuOverlay.classList.toggle('active');
            document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
        });
    }
    
    // Cerrar el menú móvil al hacer clic en el overlay
    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', function() {
            hamburgerBtn.classList.remove('active');
            mobileMenu.classList.remove('active');
            mobileMenuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
    
    // Cerrar el menú móvil al hacer clic en un enlace
    const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
    mobileNavLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            hamburgerBtn.classList.remove('active');
            mobileMenu.classList.remove('active');
            mobileMenuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
    
    // Ajustar el menú en caso de redimensión de la ventana
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            hamburgerBtn.classList.remove('active');
            mobileMenu.classList.remove('active');
            mobileMenuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});
</script>