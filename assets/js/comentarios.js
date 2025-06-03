// Archivo: assets/js/comentarios.js
// JavaScript para el sistema de comentarios

// Función para reaccionar a un comentario
function reaccionar(idComentario, tipo) {
  fetch("procesar-reaccion.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      id_comentario: idComentario,
      tipo: tipo,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Actualizar contadores
        const commentElement = document.querySelector(`[data-comment-id="${idComentario}"]`)
        const likeBtn = commentElement.querySelector(".like-btn")
        const dislikeBtn = commentElement.querySelector(".dislike-btn")
        const likeCount = commentElement.querySelector(".like-count")
        const dislikeCount = commentElement.querySelector(".dislike-count")

        // Actualizar conteos
        likeCount.textContent = data.likes
        dislikeCount.textContent = data.dislikes

        // Actualizar clases activas
        likeBtn.classList.remove("active")
        dislikeBtn.classList.remove("active")

        if (data.action === "added" || data.action === "updated") {
          if (tipo === "like") {
            likeBtn.classList.add("active")
          } else {
            dislikeBtn.classList.add("active")
          }
        }
      } else {
        alert(data.message || "Error al procesar la reacción")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      alert("Error de conexión")
    })
}

// Mostrar formulario de respuesta
function mostrarFormularioRespuesta(idComentario) {
  const form = document.getElementById(`reply-form-${idComentario}`)
  form.style.display = "block"
  form.querySelector("textarea").focus()
}

// Ocultar formulario de respuesta
function ocultarFormularioRespuesta(idComentario) {
  const form = document.getElementById(`reply-form-${idComentario}`)
  form.style.display = "none"
  form.querySelector("textarea").value = ""
}

// Mostrar opciones de moderación
function mostrarOpcionesModeracion(idComentario, idUsuario) {
  const modal = document.createElement("div")
  modal.className = "modal-overlay"
  modal.innerHTML = `
        <div class="modal-content">
            <h3>Opciones de Moderación</h3>
            <div class="moderation-options">
                <button onclick="moderarComentario(${idComentario}, 'eliminar')" class="btn btn-warning">
                    Eliminar Comentario
                </button>
                <button onclick="mostrarFormularioBan(${idComentario}, ${idUsuario})" class="btn btn-danger">
                    Banear Usuario
                </button>
                <button onclick="moderarComentario(${idComentario}, 'restaurar')" class="btn btn-success">
                    Restaurar Comentario
                </button>
            </div>
            <button onclick="cerrarModal()" class="btn btn-secondary">Cancelar</button>
        </div>
    `

  document.body.appendChild(modal)
}

// Mostrar formulario de ban
function mostrarFormularioBan(idComentario, idUsuario) {
  const modal = document.querySelector(".modal-overlay")
  modal.innerHTML = `
        <div class="modal-content">
            <h3>Banear Usuario</h3>
            <form onsubmit="procesarBan(event, ${idComentario}, ${idUsuario})">
                <div class="form-group">
                    <label>Razón del ban:</label>
                    <textarea name="razon" rows="3" required>Violación de las normas de la comunidad</textarea>
                </div>
                <div class="form-group">
                    <label>Duración (días, 0 = permanente):</label>
                    <input type="number" name="duracion_dias" value="7" min="0">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-danger">Banear</button>
                    <button type="button" onclick="cerrarModal()" class="btn btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    `
}

// Procesar ban de usuario
function procesarBan(event, idComentario, idUsuario) {
  event.preventDefault()

  const form = event.target
  const formData = new FormData(form)
  formData.append("accion", "banear_usuario")
  formData.append("id_comentario", idComentario)

  fetch("moderar-comentario.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (response.ok) {
        location.reload()
      } else {
        alert("Error al procesar el ban")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      alert("Error de conexión")
    })
}

// Moderar comentario
function moderarComentario(idComentario, accion) {
  if (confirm(`¿Estás seguro de que quieres ${accion} este comentario?`)) {
    const formData = new FormData()
    formData.append("accion", accion)
    formData.append("id_comentario", idComentario)

    fetch("moderar-comentario.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (response.ok) {
          location.reload()
        } else {
          alert("Error al moderar comentario")
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error de conexión")
      })
  }
}

// Cerrar modal
function cerrarModal() {
  const modal = document.querySelector(".modal-overlay")
  if (modal) {
    modal.remove()
  }
}

// Cerrar modal al hacer clic fuera
document.addEventListener("click", (event) => {
  if (event.target.classList.contains("modal-overlay")) {
    cerrarModal()
  }
})
