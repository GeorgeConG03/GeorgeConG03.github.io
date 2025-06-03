<?php
// Página de ejemplo de reseña de Barry con estilo comic en azul
require_once __DIR__ . '/includes/init.php';

$page_title = "Barry - Full Moon, Full Life";
$include_base_css = true;

$page_styles = '
/* Estilos para reseña estilo comic en azul */
.review-comic-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 2rem;
    position: relative;
}

.review-comic-title {
    background-color: white;
    border: 4px solid black;
    border-radius: 20px;
    padding: 1rem 2rem;
    margin: 0 auto 2rem;
    max-width: 600px;
    text-align: center;
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.7);
    position: relative;
}

.review-comic-title::before {
    content: "";
    position: absolute;
    bottom: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 20px solid transparent;
    border-right: 20px solid transparent;
    border-top: 20px solid white;
    filter: drop-shadow(4px 4px 0 rgba(0, 0, 0, 0.7));
}

.review-comic-title::after {
    content: "";
    position: absolute;
    bottom: -24px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 24px solid transparent;
    border-right: 24px solid transparent;
    border-top: 24px solid black;
    z-index: -1;
}

.review-comic-title h1 {
    font-family: "Bangers", cursive;
    font-size: 2.5rem;
    color: black;
    text-shadow: 3px 3px 0 #0066cc;
    margin: 0;
    letter-spacing: 2px;
}

.review-main-content {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 2rem;
    margin-top: 3rem;
}

.review-text-content {
    background-color: white;
    border: 4px solid #0066cc;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.7);
    position: relative;
}

.review-text-content::before {
    content: "";
    position: absolute;
    top: -8px;
    left: -8px;
    right: -8px;
    bottom: -8px;
    border: 4px solid black;
    border-radius: 20px;
    z-index: -1;
}

.review-text {
    font-size: 1.1rem;
    line-height: 1.6;
    color: black;
    text-align: justify;
}

.review-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.review-cover {
    background-color: white;
    border: 4px solid black;
    border-radius: 15px;
    padding: 1rem;
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.7);
    position: relative;
}

.review-cover img {
    width: 100%;
    height: auto;
    border-radius: 10px;
    display: block;
}

.review-score-container {
    position: absolute;
    top: -15px;
    right: -15px;
    background-color: #0066cc;
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 4px solid black;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: "Bangers", cursive;
    font-size: 2rem;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    z-index: 10;
}

.review-rating {
    text-align: center;
    margin: 1rem 0;
}

.review-stars {
    font-size: 2rem;
    color: #ffcc00;
    text-shadow: 1px 1px 0 black;
}

.review-metadata {
    background-color: white;
    border: 4px solid black;
    border-radius: 15px;
    padding: 1rem;
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.7);
}

.metadata-item {
    background-color: #0066cc;
    color: white;
    font-family: "Bangers", cursive;
    font-size: 1.1rem;
    padding: 0.5rem 1rem;
    border: 2px solid black;
    border-radius: 10px;
    margin-bottom: 0.5rem;
    text-align: center;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.5);
    letter-spacing: 1px;
}

.metadata-item:last-child {
    margin-bottom: 0;
}

.comments-section {
    margin-top: 3rem;
    background-color: white;
    border: 4px solid #0066cc;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.7);
    position: relative;
}

.comments-section::before {
    content: "";
    position: absolute;
    top: -8px;
    left: -8px;
    right: -8px;
    bottom: -8px;
    border: 4px solid black;
    border-radius: 20px;
    z-index: -1;
}

.comments-title {
    font-family: "Bangers", cursive;
    font-size: 2rem;
    color: black;
    text-shadow: 2px 2px 0 #0066cc;
    margin-bottom: 1.5rem;
    text-align: center;
}

.comment-item {
    background-color: rgba(0, 102, 204, 0.1);
    border: 2px solid #0066cc;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.3);
}

.comment-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.comment-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #0066cc;
    background-color: white;
}

.comment-user {
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    color: #0066cc;
}

.comment-stars {
    color: #ffcc00;
    font-size: 1.2rem;
}

.comment-text {
    font-size: 1rem;
    line-height: 1.4;
    color: black;
}

.comment-actions {
    margin-top: 0.5rem;
    display: flex;
    gap: 1rem;
}

.comment-action {
    font-family: "Bangers", cursive;
    font-size: 0.9rem;
    color: #0066cc;
    text-decoration: none;
    padding: 0.2rem 0.5rem;
    border-radius: 5px;
    transition: background-color 0.2s;
}

.comment-action:hover {
    background-color: rgba(0, 102, 204, 0.2);
}

@media (max-width: 768px) {
    .review-main-content {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .review-comic-title h1 {
        font-size: 2rem;
    }
    
    .review-text-content,
    .comments-section {
        padding: 1.5rem;
    }
}
';

include 'includes/header.php';
?>

<div class="review-comic-container">
    <!-- Título estilo globo de cómic -->
    <div class="review-comic-title">
        <h1>BARRY</h1>
    </div>
    
    <!-- Contenido principal -->
    <div class="review-main-content">
        <!-- Texto de la reseña -->
        <div class="review-text-content">
            <div class="review-text">
                <p><strong>BARRY</strong> es una serie de HBO que se estrenó en 2018 y que redefine el concepto de comedia negra. La serie sigue a Barry Berkman, un sicario de Cleveland que viaja a Los Ángeles para un trabajo y termina inscribiéndose en una clase de actuación. Lo que comienza como una misión se convierte en una búsqueda de identidad y propósito.</p>
                
                <p>Bill Hader no solo protagoniza la serie, sino que también la co-crea, escribe y dirige varios episodios, demostrando un talento multifacético excepcional. La serie logra equilibrar perfectamente momentos de humor absurdo con escenas de violencia brutal y reflexiones profundas sobre la moralidad, la identidad y la búsqueda de la redención.</p>
                
                <p>La trama sigue a Barry mientras intenta dejar atrás su vida como asesino a sueldo para perseguir su sueño de convertirse en actor. Sin embargo, su pasado violento constantemente lo alcanza, creando situaciones que van desde lo hilarante hasta lo profundamente perturbador. La serie explora temas como la masculinidad tóxica, el trauma de guerra, y la dificultad de cambiar cuando el mundo te ve de una sola manera.</p>
                
                <p>Cada episodio combina acción vertiginosa, humor irreverente y momentos de introspección, todo envuelto en una estética visual que recuerda a un cómic clásico pero con toques modernos. En este universo, la verdadera fuerza de Barry radica en su capacidad para conectar con la gente común, convirtiéndose en un símbolo de esperanza y humanidad en medio del caos.</p>
            </div>
        </div>
        
        <!-- Sidebar con imagen y metadatos -->
        <div class="review-sidebar">
            <!-- Portada con puntuación -->
            <div class="review-cover">
                <div class="review-score-container">9</div>
                <img src="assets/rec/barry.jpg" alt="Barry Serie">
                <div class="review-rating">
                    <div class="review-stars">★★★★★</div>
                </div>
            </div>
            
            <!-- Metadatos -->
            <div class="review-metadata">
                <div class="metadata-item">COMEDIA, DRAMA, CRIMEN</div>
                <div class="metadata-item">4 TEMPORADAS, 32 EPISODIOS</div>
            </div>
        </div>
    </div>
    
    <!-- Sección de comentarios -->
    <div class="comments-section">
        <h2 class="comments-title">COMENTARIOS:</h2>
        
        <div class="comment-item">
            <div class="comment-header">
                <div class="comment-avatar"></div>
                <span class="comment-user">USUARIO:</span>
                <div class="comment-stars">★★★★★</div>
            </div>
            <div class="comment-text">¡INCREÍBLE!</div>
            <div class="comment-actions">
                <a href="#" class="comment-action">EDITAR</a>
                <a href="#" class="comment-action">BORRAR</a>
            </div>
        </div>
        
        <div class="comment-item">
            <div class="comment-header">
                <div class="comment-avatar"></div>
                <span class="comment-user">FANDESERIES:</span>
                <div class="comment-stars">★★★★★</div>
            </div>
            <div class="comment-text">Bill Hader es un genio. La forma en que maneja el tono entre comedia y drama es magistral. Cada temporada supera a la anterior.</div>
            <div class="comment-actions">
                <a href="#" class="comment-action">EDITAR</a>
                <a href="#" class="comment-action">BORRAR</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>