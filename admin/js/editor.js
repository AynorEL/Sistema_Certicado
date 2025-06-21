document.addEventListener("DOMContentLoaded", function () {
    const editor = document.getElementById("editor");
  
    window.agregarCampo = function (tipo) {
      const campo = document.createElement("div");
      campo.className = "campo-draggable";
      campo.setAttribute("data-type", tipo);
      campo.setAttribute("contenteditable", tipo !== "qr");
      campo.innerHTML =
        tipo === "qr"
          ? '<img src="assets/qr_placeholder.png">'
          : tipo.toUpperCase();
      campo.style.top = "100px";
      campo.style.left = "100px";
      editor.appendChild(campo);
      hacerArrastrable(campo);
    };
  
    function hacerArrastrable(elemento) {
      let offsetX, offsetY;
      elemento.onmousedown = function (e) {
        e.preventDefault();
        offsetX = e.clientX - elemento.offsetLeft;
        offsetY = e.clientY - elemento.offsetTop;
  
        document.onmousemove = function (e) {
          elemento.style.left = e.clientX - offsetX + "px";
          elemento.style.top = e.clientY - offsetY + "px";
        };
  
        document.onmouseup = function () {
          document.onmousemove = null;
          document.onmouseup = null;
        };
      };
    }
  
    window.guardarConfig = function () {
      const campos = document.querySelectorAll(".campo-draggable");
      let config = [];
      campos.forEach((campo) => {
        config.push({
          tipo: campo.getAttribute("data-type"),
          texto: campo.innerText,
          left: campo.style.left,
          top: campo.style.top,
        });
      });
  
      const idCurso = document.getElementById("editor").dataset.idcurso || 1;
  
      fetch("guardar_config_certificado.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ idcurso: idCurso, config }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            alert("Diseño guardado correctamente");
          } else {
            alert("Error al guardar el diseño");
          }
        })
        .catch(() => alert("Fallo de red al guardar"));
    };
  });
  