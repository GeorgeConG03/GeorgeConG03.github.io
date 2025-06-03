// Archivo: assets/js/foros.js
// JavaScript para el sistema de foros

// Función para unirse a un foro
function unirseAlForo(idForo) {
  fetch("../procesar-foro-accion.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      accion: "unirse",
      id_foro: idForo,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        location.reload()
      } else {
        alert(data.message || "Error al unirse al foro")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      alert("Error de conexión")
    })
}

// Función para salir de un foro
function salirDelForo(idForo) {
  if (confirm("¿Estás seguro de que quieres salir de este foro?")) {
    fetch("../procesar-foro-accion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        accion: "salir",
        id_foro: idForo,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          location.reload()
        } else {
          alert(data.message || "Error al salir del foro")
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error de conexión")
      })
  }
}

// Función para reaccionar a un post
function reaccionarPost(idPost, tipo) {
  const accion = tipo === "like" ? "like_post" : "dislike_post"

  fetch("../procesar-foro-accion.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      accion: accion,
      id_post: idPost,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Actualizar contadores
        const postElement = document.querySelector(`[data-post-id="${idPost}"]`)
        const likeBtn = postElement.querySelector(".like-btn")
        const dislikeBtn = postElement.querySelector(".dislike-btn")
        const likeCount = postElement.querySelector(".like-count")
        const dislikeCount = postElement.querySelector(".dislike-count")

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

// Mostrar formulario de respuesta a post
function mostrarFormularioRespuestaPost(idPost) {
  const form = document.getElementById(`reply-form-post-${idPost}`)
  form.style.display = "block"
  form.querySelector("textarea").focus()
}

// Ocultar formulario de respuesta a post
function ocultarFormularioRespuestaPost(idPost) {
  const form = document.getElementById(`reply-form-post-${idPost}`)
  form.style.display = "none"
  form.querySelector("textarea").value = ""
}

// Mostrar opciones de moderación para posts
function mostrarOpcionesModeracionPost(idPost, idUsuario) {
  const modal = document.createElement("div")
  modal.className = "modal-overlay"
  modal.innerHTML = `
        <div class="modal-content">
            <h3>Opciones de Moderación</h3>
            <div class="moderation-options">
                <button onclick="moderarPost(${idPost}, 'eliminar')" class="btn btn-warning">
                    🗑️ Eliminar Post
                </button>
                <button onclick="moderarPost(${idPost}, 'ocultar')" class="btn btn-secondary">
                    👁️ Ocultar Post
                </button>
                <button onclick="mostrarFormularioBanUsuario(${idPost}, ${idUsuario})" class="btn btn-danger">
                    🚫 Banear Usuario
                </button>
                <button onclick="moderarPost(${idPost}, 'restaurar')" class="btn btn-success">
                    ✅ Restaurar Post
                </button>
            </div>
            <button onclick="cerrarModal()" class="btn btn-secondary">Cancelar</button>
        </div>
    `

  document.body.appendChild(modal)
}

// Mostrar formulario de ban para usuario
function mostrarFormularioBanUsuario(idPost, idUsuario) {
  const modal = document.querySelector(".modal-overlay")
  modal.innerHTML = `
        <div class="modal-content">
            <h3>Banear Usuario del Foro</h3>
            <form onsubmit="procesarBanUsuario(event, ${idPost}, ${idUsuario})">
                <div class="form-group">
                    <label>Razón del ban:</label>
                    <textarea name="razon" rows="3" required>Violación de las normas del foro</textarea>
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
function procesarBanUsuario(event, idPost, idUsuario) {
  event.preventDefault()

  const form = event.target
  const formData = new FormData(form)
  formData.append("accion", "banear_usuario")
  formData.append("id_post", idPost)
  formData.append("id_usuario", idUsuario)

  fetch("../moderar-foro.php", {
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

// Moderar post
function moderarPost(idPost, accion) {
  if (confirm(`¿Estás seguro de que quieres ${accion} este post?`)) {
    const formData = new FormData()
    formData.append("accion", accion)
    formData.append("id_post", idPost)

    fetch("../moderar-foro.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (response.ok) {
          location.reload()
        } else {
          alert("Error al moderar post")
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

// Previsualizar imagen antes de subir
document.addEventListener("DOMContentLoaded", () => {
  const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]')

  imageInputs.forEach((input) => {
    input.addEventListener("change", (event) => {
      const file = event.target.files[0]
      if (file) {
        const reader = new FileReader()
        reader.onload = (e) => {
          // Crear o actualizar preview
          let preview = input.parentNode.querySelector(".image-preview")
          if (!preview) {
            preview = document.createElement("div")
            preview.className = "image-preview"
            preview.style.marginTop = "10px"
            input.parentNode.appendChild(preview)
          }

          preview.innerHTML = `
                        <img src="${e.target.result}" style="max-width: 200px; max-height: 200px; border-radius: 5px; border: 2px solid #ddd;">
                        <button type="button" onclick="this.parentNode.remove(); this.parentNode.previousElementSibling.value = '';" style="display: block; margin-top: 5px; padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer;">
                            Eliminar imagen
                        </button>
                    `
        }
        reader.readAsDataURL(file)
      }
    })
  })
})
