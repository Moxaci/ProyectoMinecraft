/**
 * CraftLog - Módulo del Dashboard
 * Maneja la carga dinámica de proyectos y estadísticas
 */

document.addEventListener('DOMContentLoaded', function() {
    // Obtener info del usuario desde la sesión
    cargarInfoUsuario();
    
    // Cargar datos al iniciar
    cargarProyectos();
    cargarEstadisticas();

    // Configurar búsqueda
    const searchInput = document.getElementById('searchProjects');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filtrarProyectos(this.value);
        });
    }

    // Configurar botón de nuevo proyecto
    const btnNuevo = document.getElementById('btnNuevoProyecto');
    if (btnNuevo) {
        btnNuevo.addEventListener('click', function(e) {
            e.preventDefault();
            mostrarModalNuevoProyecto();
        });
    }

    // Configurar tarjeta "Nuevo proyecto" del grid
    const btnAddCard = document.getElementById('btnAddCard');
    if (btnAddCard) {
        btnAddCard.addEventListener('click', function(e) {
            e.preventDefault();
            mostrarModalNuevoProyecto();
        });
    }
});

/**
 * Carga la información del usuario desde la sesión
 */
function cargarInfoUsuario() {
    fetch('/ProyectoM/api/usuario_info.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => {
        if (response.status === 401) {
            window.location.href = 'login.html';
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            actualizarInfoUsuario(data.usuario);
        }
    })
    .catch(error => {
        console.error('Error al cargar información del usuario:', error);
    });
}

/**
 * Actualiza el nombre del usuario en la interfaz
 */
function actualizarInfoUsuario(usuario) {
    // Sidebar - nombre del usuario
    const sidebarName = document.querySelector('.sidebar .avatar + div div:first-child');
    if (sidebarName) {
        sidebarName.textContent = usuario.nombre || 'Usuario';
    }

    // Sidebar - iniciales en el avatar
    const avatarElements = document.querySelectorAll('.avatar');
    const iniciales = getIniciales(usuario.nombre || 'Usuario');
    avatarElements.forEach(el => {
        if (!el.id || el.id !== 'profileBtn') {
            el.textContent = iniciales;
        }
    });

    // Dropdown de perfil
    const dropdownName = document.querySelector('#dropdownMenu div:first-child div:first-child');
    if (dropdownName) {
        dropdownName.textContent = usuario.nombre || 'Usuario';
    }

    // Botón de perfil
    const profileBtn = document.getElementById('profileBtn');
    if (profileBtn) {
        profileBtn.textContent = iniciales;
    }
}

/**
 * Obtiene las iniciales de un nombre
 */
function getIniciales(nombre) {
    return nombre
        .split(' ')
        .map(palabra => palabra.charAt(0).toUpperCase())
        .slice(0, 2)
        .join('');
}

/**
 * Carga los proyectos desde el servidor
 */
function cargarProyectos() {
    fetch('/ProyectoM/api/proyectos.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => {
        if (response.status === 401) {
            window.location.href = 'login.html';
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            renderizarProyectos(data.proyectos);
            actualizarSidebarRecientes(data.proyectos);
        } else if (data && data.error) {
            mostrarError(data.error);
        }
    })
    .catch(error => {
        console.error('Error al cargar proyectos:', error);
        mostrarError('Error al cargar los proyectos');
    });
}

/**
 * Actualiza el sidebar con los proyectos recientes
 */
function actualizarSidebarRecientes(proyectos) {
    if (!proyectos || proyectos.length === 0) return;

    // Limpiar secciones del sidebar (mantener solo la primera)
    const sidebar = document.querySelector('.sidebar-history');
    if (!sidebar) return;

    // Limpiar todas las secciones excepto la primera
    const secciones = sidebar.querySelectorAll('.history-section');
    secciones.forEach((seccion, index) => {
        if (index > 0) seccion.remove();
    });

    // Obtener la primera sección
    const seccionReciente = secciones[0];
    if (!seccionReciente) return;

    // Limpiar items existentes
    const itemsExistentes = seccionReciente.querySelectorAll('.history-item');
    itemsExistentes.forEach(item => item.remove());

    // Mostrar hasta 5 proyectos recientes
    const recientes = proyectos.slice(0, 5);
    recientes.forEach(proyecto => {
        const item = document.createElement('a');
        item.href = `proyecto.html?id=${proyecto.id_proyecto}`;
        item.className = 'history-item';
        item.innerHTML = `
            <div class="history-item-icon" style="background:${obtenerColorProyecto(proyecto.nombre)}"></div>
            ${proyecto.nombre}
        `;
        seccionReciente.appendChild(item);
    });
}

/**
 * Renderiza los proyectos en el grid
 */
function renderizarProyectos(proyectos) {
    const grid = document.getElementById('projectsGrid');
    if (!grid) return;

    // Eliminar proyectos existentes (excepto la tarjeta "añadir")
    const cards = grid.querySelectorAll('.project-card:not(.add-card)');
    cards.forEach(card => card.remove());

    if (!proyectos || proyectos.length === 0) {
        // Mostrar mensaje si no hay proyectos
        const emptyMsg = document.createElement('div');
        emptyMsg.className = 'project-card';
        emptyMsg.style.gridColumn = '1 / -1';
        emptyMsg.style.textAlign = 'center';
        emptyMsg.style.padding = '40px';
        emptyMsg.style.background = 'transparent';
        emptyMsg.style.border = '1px dashed var(--border)';
        emptyMsg.innerHTML = `
            <div style="font-size:28px; margin-bottom:12px;">🏗️</div>
            <div style="font-size:16px; color:var(--text-muted); font-weight:500;">No tienes proyectos aún</div>
            <div style="font-size:13px; color:var(--text-muted); margin-top:8px;">
                Haz clic en <strong>"Nuevo proyecto"</strong> para empezar
            </div>
        `;
        grid.insertBefore(emptyMsg, grid.querySelector('.add-card'));
        return;
    }

    // Renderizar cada proyecto
    proyectos.forEach(proyecto => {
        const card = document.createElement('div');
        card.className = 'project-card';
        card.dataset.id = proyecto.id_proyecto;
        card.dataset.nombre = proyecto.nombre.toLowerCase();

        // Determinar color de bloque según el nombre
        const color = obtenerColorProyecto(proyecto.nombre);

        // Formatear fecha
        const fecha = formatearFecha(proyecto.fecha_creacion);

        card.innerHTML = `
            <div class="project-card-header" onclick="window.location.href='proyecto.html?id=${proyecto.id_proyecto}'">
                <div class="pixel-block project-block-icon" style="background:${color}; width:32px; height:32px; border-radius:4px; flex-shrink:0;"></div>
                <div>
                    <div class="project-card-name">${proyecto.nombre}</div>
                    <div class="project-card-meta">${fecha}</div>
                </div>
            </div>
            <div class="project-card-footer">
                <span class="badge badge-stone">${proyecto.total_items || 0} items</span>
                <div style="display:flex; gap:8px; align-items:center;">
                    <span class="text-xs text-muted">${formatearNumero(proyecto.total_materiales || 0)} materiales</span>
                    <button class="btn btn-ghost btn-sm" onclick="event.stopPropagation(); eliminarProyecto(${proyecto.id_proyecto})" style="color:var(--text-red); padding:4px 8px; font-size:12px;">✕</button>
                </div>
            </div>
        `;

        grid.insertBefore(card, grid.querySelector('.add-card'));
    });
}

/**
 * Carga las estadísticas desde el servidor
 */
function cargarEstadisticas() {
    fetch('/ProyectoM/api/estadisticas.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.success) {
            actualizarEstadisticas(data.estadisticas);
        }
    })
    .catch(error => {
        console.error('Error al cargar estadísticas:', error);
    });
}

/**
 * Actualiza los valores en las tarjetas de estadísticas
 */
function actualizarEstadisticas(estadisticas) {
    const statProyectos = document.getElementById('statProyectos');
    const statMateriales = document.getElementById('statMateriales');
    const statHoras = document.getElementById('statHoras');

    if (statProyectos) {
        // Animación de conteo
        animarNumero(statProyectos, statProyectos.textContent, estadisticas.total_proyectos || 0);
    }
    if (statMateriales) {
        animarNumero(statMateriales, statMateriales.textContent.replace(/,/g, ''), estadisticas.total_materiales || 0);
    }
    if (statHoras) {
        animarNumero(statHoras, statHoras.textContent, estadisticas.total_hornos || 0);
    }
}

/**
 * Animación de conteo de números
 */
function animarNumero(elemento, valorInicial, valorFinal) {
    if (parseInt(valorInicial) === parseInt(valorFinal)) return;
    
    const duracion = 500;
    const inicio = performance.now();
    const inicial = parseInt(valorInicial) || 0;
    const final = parseInt(valorFinal) || 0;
    
    function actualizar(timestamp) {
        const progreso = Math.min((timestamp - inicio) / duracion, 1);
        const valorActual = Math.round(inicial + (final - inicial) * progreso);
        
        if (final >= 1000) {
            elemento.textContent = formatearNumero(valorActual);
        } else if (Number.isInteger(final)) {
            elemento.textContent = valorActual;
        } else {
            elemento.textContent = (inicial + (final - inicial) * progreso).toFixed(1);
        }
        
        if (progreso < 1) {
            requestAnimationFrame(actualizar);
        } else {
            if (final >= 1000) {
                elemento.textContent = formatearNumero(final);
            } else {
                elemento.textContent = final;
            }
        }
    }
    requestAnimationFrame(actualizar);
}

/**
 * Filtra proyectos por búsqueda
 */
function filtrarProyectos(query) {
    const q = query.toLowerCase().trim();
    const cards = document.querySelectorAll('.project-card:not(.add-card)');
    
    cards.forEach(card => {
        const nombre = card.dataset.nombre || '';
        card.style.display = nombre.includes(q) ? '' : 'none';
    });
}

/**
 * Muestra un modal para crear nuevo proyecto (estilo VS Code)
 */
function mostrarModalNuevoProyecto() {
    // Crear overlay
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(4px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        animation: fadeIn 0.2s ease;
    `;

    // Crear modal
    const modal = document.createElement('div');
    modal.style.cssText = `
        background: var(--bg-card);
        border: 1px solid var(--border-strong);
        border-radius: 8px;
        padding: 32px;
        min-width: 400px;
        max-width: 90%;
        box-shadow: 0 24px 64px rgba(0,0,0,0.6);
        animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    `;

    modal.innerHTML = `
        <div style="margin-bottom:24px;">
            <div style="font-family:var(--font-pixel); font-size:8px; color:var(--text-muted); letter-spacing:1px; margin-bottom:8px;">
                📁 NUEVO PROYECTO
            </div>
            <div style="font-size:20px; font-weight:600; color:var(--text);">
                ¿Cómo se llamará tu proyecto?
            </div>
            <div style="font-size:13px; color:var(--text-muted); margin-top:4px;">
                Escribe un nombre descriptivo para tu proyecto
            </div>
        </div>

        <div style="margin-bottom:24px;">
            <label style="display:block; font-size:12px; color:var(--text-secondary); margin-bottom:6px; font-weight:500;">
                Nombre del proyecto
            </label>
            <input 
                type="text" 
                id="inputNombreProyecto" 
                placeholder="Ej: Castillo de Piedra, Granja de Hierro..."
                style="
                    width: 100%;
                    padding: 10px 14px;
                    font-size: 14px;
                    background: var(--bg);
                    border: 2px solid var(--border-strong);
                    border-radius: 4px;
                    color: var(--text);
                    outline: none;
                    transition: border-color 0.2s;
                "
                autofocus
            >
            <div id="errorNombreProyecto" style="color:var(--text-red); font-size:12px; margin-top:4px; display:none;"></div>
        </div>

        <div style="display:flex; gap:12px; justify-content:flex-end;">
            <button id="btnCancelarProyecto" style="
                padding: 8px 20px;
                background: transparent;
                border: 1px solid var(--border-strong);
                border-radius: 4px;
                color: var(--text-secondary);
                cursor: pointer;
                font-size: 13px;
                transition: all 0.2s;
            ">
                Cancelar
            </button>
            <button id="btnCrearProyecto" style="
                padding: 8px 24px;
                background: var(--mc-diamond);
                border: none;
                border-radius: 4px;
                color: #1a1a2e;
                cursor: pointer;
                font-size: 13px;
                font-weight: 600;
                transition: all 0.2s;
            ">
                ✨ Crear proyecto
            </button>
        </div>
    `;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    // Enfocar el input
    const input = document.getElementById('inputNombreProyecto');
    setTimeout(() => input.focus(), 100);

    // Evento para crear (Enter)
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            crearNuevoProyecto(this.value, overlay);
        }
    });

    // Evento para cancelar (Escape)
    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') {
            overlay.remove();
            document.removeEventListener('keydown', escHandler);
        }
    });

    // Botón Cancelar
    document.getElementById('btnCancelarProyecto').addEventListener('click', function() {
        overlay.remove();
    });

    // Botón Crear
    document.getElementById('btnCrearProyecto').addEventListener('click', function() {
        const nombre = input.value.trim();
        crearNuevoProyecto(nombre, overlay);
    });

    // Clic en overlay para cerrar
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) overlay.remove();
    });

    // Estilos para las animaciones (si no existen)
    if (!document.getElementById('modalStyles')) {
        const style = document.createElement('style');
        style.id = 'modalStyles';
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideUp {
                from { transform: translateY(20px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Crea un nuevo proyecto
 */
function crearNuevoProyecto(nombre, overlay) {
    const errorEl = document.getElementById('errorNombreProyecto');
    errorEl.style.display = 'none';

    if (!nombre || nombre.trim() === '') {
        errorEl.textContent = '⚠️ El nombre del proyecto es obligatorio';
        errorEl.style.display = 'block';
        return;
    }

    if (nombre.trim().length < 3) {
        errorEl.textContent = '⚠️ El nombre debe tener al menos 3 caracteres';
        errorEl.style.display = 'block';
        return;
    }

    // Deshabilitar botón
    const btnCrear = document.getElementById('btnCrearProyecto');
    btnCrear.disabled = true;
    btnCrear.textContent = '⏳ Creando...';

    const params = new URLSearchParams({
        nombre: nombre.trim(),
        descripcion: ''
    });

    fetch('/ProyectoM/api/crear_proyecto.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: params
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            overlay.remove();
            // Recargar datos
            cargarProyectos();
            cargarEstadisticas();
            // Feedback visual
            mostrarFeedback('✅ Proyecto creado exitosamente', 'success');
        } else {
            errorEl.textContent = '❌ ' + (data.error || 'No se pudo crear el proyecto');
            errorEl.style.display = 'block';
            btnCrear.disabled = false;
            btnCrear.textContent = '✨ Crear proyecto';
        }
    })
    .catch(error => {
        errorEl.textContent = '❌ Error al conectar con el servidor';
        errorEl.style.display = 'block';
        btnCrear.disabled = false;
        btnCrear.textContent = '✨ Crear proyecto';
        console.error(error);
    });
}

/**
 * Elimina un proyecto
 */
function eliminarProyecto(id) {
    // Modal de confirmación personalizado
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(4px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        animation: fadeIn 0.2s ease;
    `;

    const modal = document.createElement('div');
    modal.style.cssText = `
        background: var(--bg-card);
        border: 1px solid var(--border-strong);
        border-radius: 8px;
        padding: 32px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 24px 64px rgba(0,0,0,0.6);
        animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: center;
    `;

    modal.innerHTML = `
        <div style="font-size:48px; margin-bottom:12px;">🗑️</div>
        <div style="font-size:18px; font-weight:600; color:var(--text); margin-bottom:8px;">
            ¿Eliminar proyecto?
        </div>
        <div style="font-size:13px; color:var(--text-muted); margin-bottom:24px;">
            Esta acción no se puede deshacer. Los materiales calculados se perderán.
        </div>
        <div style="display:flex; gap:12px; justify-content:center;">
            <button id="confirmarEliminar" style="
                padding: 8px 24px;
                background: #ff4444;
                border: none;
                border-radius: 4px;
                color: white;
                cursor: pointer;
                font-size: 13px;
                font-weight: 600;
                transition: all 0.2s;
            ">
                Sí, eliminar
            </button>
            <button id="cancelarEliminar" style="
                padding: 8px 20px;
                background: transparent;
                border: 1px solid var(--border-strong);
                border-radius: 4px;
                color: var(--text-secondary);
                cursor: pointer;
                font-size: 13px;
                transition: all 0.2s;
            ">
                Cancelar
            </button>
        </div>
    `;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    document.getElementById('confirmarEliminar').addEventListener('click', function() {
        overlay.remove();
        
        const params = new URLSearchParams({
            id: id,
            _method: 'DELETE'
        });

        fetch('/ProyectoM/api/eliminar_proyecto.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cargarProyectos();
                cargarEstadisticas();
                mostrarFeedback('✅ Proyecto eliminado', 'success');
            } else {
                mostrarFeedback('❌ ' + (data.error || 'Error al eliminar'), 'error');
            }
        })
        .catch(error => {
            mostrarFeedback('❌ Error al eliminar el proyecto', 'error');
            console.error(error);
        });
    });

    document.getElementById('cancelarEliminar').addEventListener('click', function() {
        overlay.remove();
    });

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) overlay.remove();
    });
}

/**
 * Muestra un feedback visual (toast)
 */
function mostrarFeedback(mensaje, tipo = 'info') {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        padding: 14px 24px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        z-index: 10000;
        animation: slideUp 0.3s ease;
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        background: ${tipo === 'success' ? '#2d5016' : '#441111'};
        color: ${tipo === 'success' ? '#8bc34a' : '#ff6b6b'};
        border: 1px solid ${tipo === 'success' ? '#4a7a2a' : '#662222'};
        max-width: 400px;
    `;
    toast.textContent = mensaje;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Utilidad: formatear fecha
 */
function formatearFecha(fecha) {
    if (!fecha) return 'Fecha desconocida';
    const date = new Date(fecha);
    const ahora = new Date();
    const diff = Math.floor((ahora - date) / (1000 * 60 * 60 * 24));
    
    if (diff === 0) return 'Hoy';
    if (diff === 1) return 'Ayer';
    if (diff < 7) return `Hace ${diff} días`;
    if (diff < 30) return `Hace ${Math.floor(diff / 7)} semanas`;
    if (diff < 365) return `Hace ${Math.floor(diff / 30)} meses`;
    return `Hace ${Math.floor(diff / 365)} años`;
}

/**
 * Utilidad: formatear números con separadores
 */
function formatearNumero(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Utilidad: obtener color según nombre del proyecto
 */
function obtenerColorProyecto(nombre) {
    const colores = [
        '#4A3728', '#1A3A5C', '#4A4A4A', '#2D5016', '#8B4513',
        '#2F4F4F', '#8B0000', '#2E2E2E', '#3D5A80', '#5C4033',
        '#2C3E50', '#6B4423', '#1C2833', '#4A235A', '#1A5276',
        '#5D3A1A', '#0D3B39', '#3E2723', '#1B2A47', '#4A2C2C'
    ];
    let hash = 0;
    for (let i = 0; i < nombre.length; i++) {
        hash = nombre.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colores[Math.abs(hash) % colores.length];
}

/**
 * Utilidad: mostrar error en la interfaz
 */
function mostrarError(mensaje) {
    mostrarFeedback('❌ ' + mensaje, 'error');
}