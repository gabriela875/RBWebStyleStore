// Validaciones en el CLIENTE. Las mismas reglas estan en includes/validaciones.php (servidor).

// Solo letras (con tildes y ñ) y espacios
function esSoloLetras(texto) {
  return /^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$/.test(texto.trim());
}

// Solo numeros
function esSoloNumeros(texto) {
  return /^[0-9]+$/.test(texto.trim());
}

// Email con formato valido
function esEmailValido(correo) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo.trim());
}

// Fecha valida AAAA-MM-DD
function esFechaValida(fecha) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(fecha)) return false;
  const d = new Date(fecha);
  return !isNaN(d.getTime());
}

// Campo obligatorio
function esRequerido(valor) {
  return valor !== null && valor !== undefined && String(valor).trim() !== '';
}

// Longitud minima (por defecto 6)
function tieneLongitudMinima(texto, min = 6) {
  return texto.trim().length >= min;
}

// Numero mayor que cero
function esMayorQueCero(valor) {
  return !isNaN(valor) && parseFloat(valor) > 0;
}

// Revisa una lista de reglas [{condicion, mensaje}] y devuelve el primer error o null.
function primerError(reglas) {
  for (const r of reglas) {
    if (!r.condicion) return r.mensaje;
  }
  return null;
}

// Limita un input a solo letras mientras se escribe (uso: oninput)
function filtrarSoloLetras(input) {
  input.value = input.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ ]/g, '');
}

// Limita un input a solo numeros mientras se escribe (uso: oninput)
function filtrarSoloNumeros(input) {
  input.value = input.value.replace(/[^0-9]/g, '');
}

// Helper para peticiones AJAX con fetch. Envia datos como form-urlencoded y espera JSON.
async function enviarAccion(url, datos) {
  const cuerpo = new URLSearchParams(datos);
  const resp = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: cuerpo.toString(),
  });
  // Si la sesion expiro, el servidor responde 401: recargamos para ir al login
  if (resp.status === 401) {
    const j = await resp.json().catch(() => ({}));
    alertaError(j.mensaje || 'Sesión expirada');
    setTimeout(() => (window.location.href = '../index.php'), 1500);
    return { ok: false, mensaje: 'Sesión expirada' };
  }
  return resp.json();
}
