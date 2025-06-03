document.addEventListener("DOMContentLoaded", () => {
  // Manejo de imágenes
  const imageUploadContainer = document.getElementById("image-upload-container")
  const imageInput = document.getElementById("imagenes")
  const previewContainer = document.getElementById("image-preview-container")

  // Evento para abrir el selector de archivos al hacer clic en el contenedor
  imageUploadContainer.addEventListener("click", () => {
    imageInput.click()
  })

  // Evento para mostrar previsualizaciones cuando se seleccionan archivos
  imageInput.addEventListener("change", function () {
    // Limpiar previsualizaciones anteriores
    previewContainer.innerHTML = ""

    // Crear previsualizaciones para cada archivo
    for (let i = 0; i < this.files.length; i++) {
      const file = this.files[i]

      // Verificar que sea una imagen
      if (!file.type.match("image.*")) {
        continue
      }

      const reader = new FileReader()

      reader.onload = (e) => {
        const previewDiv = document.createElement("div")
        previewDiv.className = "image-preview"

        const img = document.createElement("img")
        img.src = e.target.result
        img.alt = "Vista previa"

        const removeBtn = document.createElement("button")
        removeBtn.className = "remove-btn"
        removeBtn.innerHTML = "×"
        removeBtn.setAttribute("data-index", i)
        removeBtn.addEventListener("click", (e) => {
          e.stopPropagation() // Evitar que se abra el selector de archivos
          previewDiv.remove()
        })

        previewDiv.appendChild(img)
        previewDiv.appendChild(removeBtn)
        previewContainer.appendChild(previewDiv)
      }

      reader.readAsDataURL(file)
    }
  })

  // Manejo de campos específicos por categoría
  const categoriaSelect = document.getElementById("categoria")
  const serieFields = document.getElementById("serie-fields")
  const peliculaFields = document.getElementById("pelicula-fields")
  const libroFields = document.getElementById("libro-fields")

  // Función para mostrar/ocultar campos según la categoría seleccionada
  function toggleCategoryFields() {
    const selectedCategory = categoriaSelect.value

    // Ocultar todos los campos específicos
    serieFields.classList.remove("active")
    peliculaFields.classList.remove("active")
    libroFields.classList.remove("active")

    // Mostrar los campos correspondientes a la categoría seleccionada
    if (selectedCategory === "serie") {
      serieFields.classList.add("active")
    } else if (selectedCategory === "pelicula") {
      peliculaFields.classList.add("active")
    } else if (selectedCategory === "libro") {
      libroFields.classList.add("active")
    }
  }

  // Evento para cambiar los campos cuando se selecciona una categoría
  categoriaSelect.addEventListener("change", toggleCategoryFields)

  // Ejecutar al cargar la página para mostrar los campos correctos si hay una categoría seleccionada
  toggleCategoryFields()
})
