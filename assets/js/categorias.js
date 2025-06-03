// catalogo.js
document.addEventListener('DOMContentLoaded', () => {
    // 1. Filtrado dinámico
    const inputBusqueda = document.getElementById('buscar');
    const selectorCategoria = document.getElementById('categoria');
    const itemsCatalogo = document.querySelectorAll('.item-catalogo');
    
    const filtrarItems = () => {
      const textoBusqueda = inputBusqueda.value.toLowerCase();
      const categoriaSeleccionada = selectorCategoria.value;
      
      itemsCatalogo.forEach(item => {
        const titulo = item.dataset.titulo.toLowerCase();
        const categoria = item.dataset.categoria;
        const coincideBusqueda = titulo.includes(textoBusqueda);
        const coincideCategoria = categoriaSeleccionada === 'todas' || categoria === categoriaSeleccionada;
        
        item.style.display = (coincideBusqueda && coincideCategoria) ? 'block' : 'none';
      });
    };
    
    inputBusqueda.addEventListener('input', filtrarItems);
    selectorCategoria.addEventListener('change', filtrarItems);
  
    // 2. Favoritos (LocalStorage)
    const btnsFavorito = document.querySelectorAll('.btn-favorito');
    
    btnsFavorito.forEach(btn => {
      const itemId = btn.dataset.id;
      btn.addEventListener('click', () => {
        btn.classList.toggle('activo');
        const favoritos = JSON.parse(localStorage.getItem('favoritos')) || [];
        
        if (btn.classList.contains('activo')) {
          favoritos.push(itemId);
        } else {
          favoritos.splice(favoritos.indexOf(itemId), 1);
        }
        
        localStorage.setItem('favoritos', JSON.stringify(favoritos));
      });
  
      // Resaltar favoritos guardados al cargar
      if (JSON.parse(localStorage.getItem('favoritos'))?.includes(itemId)) {
        btn.classList.add('activo');
      }
    });
  });