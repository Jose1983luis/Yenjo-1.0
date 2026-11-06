
/*
 * =========================================================
 * YENJO 1.0
 * API.JS
 * Capa centralizada de comunicación Frontend - Backend
 * =========================================================
 */

const API_BASE_URL = '../backend/api/index.php';

/**
 * Realiza solicitudes HTTP a la API REST de YENJO.
 *
 * @param {string} recurso - Recurso de la API.
 * @param {object} opciones - Configuración de la solicitud.
 * @returns {Promise<object>} Respuesta de la API.
 */

async function apiRequest(recurso, opciones = {}) {

    const {
        metodo = 'GET',
        parametros = {},
        datos = null
    } = opciones;

    const url = new URL(API_BASE_URL, window.location.href);

    url.searchParams.set('recurso', recurso);

    Object.entries(parametros).forEach(([clave, valor]) => {
        if (valor !== null && valor !== undefined) {
            url.searchParams.set(clave, valor);
        }
    });

    const configuracion = {
        method: metodo,
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json'
        }
    };

    if (datos !== null) {
        configuracion.headers['Content-Type'] = 'application/json';
        configuracion.body = JSON.stringify(datos);
    }

    let respuesta;

    try {
        respuesta = await fetch(url, configuracion);
    } catch (error) {

        const nuevoError = new Error(
            'No fue posible conectar con el servidor de YENJO.'
        );

        nuevoError.causa = error;

        throw nuevoError;
    }

    const contenido = await respuesta.text();

    let resultado = null;

    if (contenido.trim() !== '') {

        try {
            resultado = JSON.parse(contenido);

        } catch (error) {

            const nuevoError = new Error(
                'El servidor devolvió una respuesta que no es JSON válida.'
            );

            nuevoError.estadoHTTP = respuesta.status;
            nuevoError.respuestaOriginal = contenido;

            throw nuevoError;
        }
    }

    if (!respuesta.ok) {

        const error = new Error(
            resultado?.mensaje ||
            `Error HTTP ${respuesta.status}`
        );

        error.estadoHTTP = respuesta.status;
        error.respuesta = resultado;

        throw error;
    }

    if (resultado === null) {

        const error = new Error(
            'El servidor devolvió una respuesta vacía.'
        );

        error.estadoHTTP = respuesta.status;

        throw error;
    }

    return resultado;
}

