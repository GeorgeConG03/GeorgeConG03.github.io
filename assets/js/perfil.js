// perfil.js
document.addEventListener('DOMContentLoaded', () => {
    // 1. Edición dinámica de campos
    const camposPerfil = document.querySelectorAll('.campo-perfil');
    const btnEditar = document.getElementById('btn-editar');
    
    btnEditar.addEventListener('click', () => {
      camposPerfil.forEach(campo => {
        const valorOriginal = campo.textContent;
        campo.innerHTML = `<input type="text" value="${valorOriginal}" class="input-editar">`;
      });
      btnEditar.textContent = 'Guardar';
      btnEditar.classList.add('guardar');
    });
  
    // 2. Vista previa de imagen
    const inputImagen = document.getElementById('foto-perfil');
    const imagenPreview = document.getElementById('imagen-preview');
    
    inputImagen.addEventListener('change', (e) => {
      const archivo = e.target.files[0];
      if (archivo) {
        const reader = new FileReader();
        reader.onload = (event) => {
          imagenPreview.src = event.target.result;
        };
        reader.readAsDataURL(archivo);
      }
    });
  
    // 3. Guardar cambios (ejemplo con Fetch API)
    document.addEventListener('click', async (e) => {
      if (e.target.classList.contains('guardar')) {
        const nuevosDatos = {};
        camposPerfil.forEach(campo => {
          const input = campo.querySelector('.input-editar');
          nuevosDatos[campo.dataset.campo] = input.value;
        });
  
        try {
          const response = await fetch('/actualizar-perfil', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(nuevosDatos)
          });
          if (response.ok) location.reload(); // Recargar tras éxito
        } catch (error) {
          console.error('Error al guardar:', error);
        }
      }
    });
  });