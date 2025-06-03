// inicio.js
document.addEventListener('DOMContentLoaded', () => {
    // 1. Carrusel automático
    const carrusel = document.querySelector('.carrusel');
    const items = document.querySelectorAll('.carrusel-item');
    let indiceActual = 0;
    
    const moverCarrusel = () => {
      indiceActual = (indiceActual + 1) % items.length;
      carrusel.style.transform = `translateX(-${indiceActual * 100}%)`;
    };
    
    let intervalo = setInterval(moverCarrusel, 5000); // Cambia cada 5 segundos
    
    // Pausar al interactuar
    carrusel.addEventListener('mouseenter', () => clearInterval(intervalo));
    carrusel.addEventListener('mouseleave', () => {
      intervalo = setInterval(moverCarrusel, 5000);
    });
  
    // 2. Lazy Loading para imágenes
    const imagenesLazy = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            observer.unobserve(img);
          }
        });
      });
      
      imagenesLazy.forEach(img => observer.observe(img));
    } else {
      // Fallback para navegadores antiguos
      imagenesLazy.forEach(img => {
        img.src = img.dataset.src;
      });
    }
  });