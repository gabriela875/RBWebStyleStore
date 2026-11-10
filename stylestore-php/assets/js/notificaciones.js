// Envoltorio de notificaciones. Usa SweetAlert2 y Toastify.
// Nunca se usa alert() en el proyecto.

// Mensaje corto de exito (esquina)
function notificarExito(mensaje) {
  Toastify({
    text: mensaje,
    duration: 3000,
    gravity: 'top',
    position: 'right',
    style: { background: '#C43670' },
  }).showToast();
}

// Mensaje corto de error (esquina)
function notificarError(mensaje) {
  Toastify({
    text: mensaje,
    duration: 4000,
    gravity: 'top',
    position: 'right',
    style: { background: '#A0273A' },
  }).showToast();
}

// Alerta de error con detalle (ventana central)
function alertaError(mensaje) {
  Swal.fire({ icon: 'error', title: 'Error', text: mensaje, confirmButtonColor: '#C43670' });
}

// Alerta de exito (ventana central)
function alertaExito(mensaje) {
  Swal.fire({ icon: 'success', title: 'Listo', text: mensaje, confirmButtonColor: '#C43670' });
}

// Dialogo de confirmacion. Devuelve una promesa que resuelve true/false.
async function confirmar(mensaje, titulo = '¿Está seguro?') {
  const r = await Swal.fire({
    title: titulo,
    text: mensaje,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#C43670',
    cancelButtonColor: '#9C6080',
    confirmButtonText: 'Sí, continuar',
    cancelButtonText: 'Cancelar',
  });
  return r.isConfirmed;
}

// Pide un texto (p. ej. motivo de anulacion). Devuelve el texto o null si se cancela.
async function pedirTexto(titulo, placeholder = '') {
  const r = await Swal.fire({
    title: titulo,
    input: 'textarea',
    inputPlaceholder: placeholder,
    showCancelButton: true,
    confirmButtonColor: '#C43670',
    cancelButtonColor: '#9C6080',
    confirmButtonText: 'Aceptar',
    cancelButtonText: 'Cancelar',
  });
  return r.isConfirmed ? r.value : null;
}
