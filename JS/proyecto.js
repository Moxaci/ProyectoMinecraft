/**
 * CraftLog - Módulo de Proyecto
 * Maneja la carga, edición, inventario y cálculo de proyectos
 */

let proyectoId = null;
let itemsDisponibles = [];
let itemsProyecto = [];
let inventarioUsuario = {}; // { itemId: cantidad }
let usuarioActual = null;

document.addEventListener('DOMContentLoaded', function() {
    // 1. Cargar información del usuario (para el avatar)
    cargarInfoUsuario();

    // 2. Obtener ID del proyecto de la URL
    const urlParams = new URLSearchParams(window.location.search);
    proyectoId = urlParams.get('id') || 0;

    if (proyectoId === 0 || proyectoId === '0') {
        crearNuevoProyecto();
    } else {
        cargarProyecto();
        cargarInventarioUsuario();
    }

    // 3. Configurar búsqueda de bloques
    const searchInput = document.getElementById('searchBlocks');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filtrarBloques(this.value);
        });
    }

    // 4. Configurar renombre del proyecto (Enter para guardar)
    const titleInput = document.getElementById('projectTitle');
    if (titleInput) {
        titleInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                this.blur();
            }
        });
        titleInput.addEventListener('blur', function() {
            renombrarProyecto(this.value);
        });
    }

    // 5. Configurar botón calcular
    document.getElementById('btnCalcularProyecto').addEventListener('click', function() {
        calcularProyecto();
    });

    // 6. Configurar botón limpiar lista
    document.getElementById('btnLimpiar').addEventListener('click', function() {
        limpiarLista();
    });

    // 7. Configurar botón agregar al inventario
    document.getElementById('btnAgregarInventario').addEventListener('click', function() {
        mostrarModalAgregarInventario();
    });

    // 8. Configurar botón limpiar inventario
    document.getElementById('btnLimpiarInventario').addEventListener('click', function() {
        limpiarInventario();
    });

    // 9. Cargar catálogo de items
    cargarCatalogo();
});

/**
 * Carga la información del usuario para el avatar y dropdown
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
            usuarioActual = data.usuario;
            actualizarAvatar(usuarioActual);
        }
    })
    .catch(error => {
        console.error('Error al cargar información del usuario:', error);
    });
}

/**
 * Actualiza el avatar y dropdown con la info del usuario
 */
function actualizarAvatar(usuario) {
    // Avatar (iniciales)
    const avatarBtn = document.getElementById('avatarBtn');
    if (avatarBtn) {
        const iniciales = getIniciales(usuario.nombre);
        avatarBtn.textContent = iniciales;
        avatarBtn.style.background = generarColorAvatar(usuario.nombre);
    }

    // Dropdown - nombre
    const dropdownName = document.getElementById('dropdownUserName');
    if (dropdownName) {
        dropdownName.textContent = usuario.nombre;
    }

    // Dropdown - email
    const dropdownEmail = document.getElementById('dropdownUserEmail');
    if (dropdownEmail) {
        dropdownEmail.textContent = usuario.correo;
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
 * Genera un color consistente para el avatar basado en el nombre
 */
function generarColorAvatar(nombre) {
    let hash = 0;
    for (let i = 0; i < nombre.length; i++) {
        hash = nombre.charCodeAt(i) + ((hash << 5) - hash);
    }
    const colores = ['#47D7E5', '#F7C948', '#7BC84A', '#E8845A', '#A78BFA', '#F472B6'];
    return colores[Math.abs(hash) % colores.length];
}

/**
 * Crea un nuevo proyecto con modal estilo VS Code
 */
function crearNuevoProyecto() {
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

    const input = document.getElementById('inputNombreProyecto');
    setTimeout(() => input.focus(), 100);

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            crearProyectoDesdeModal(input.value, overlay);
        }
    });

    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') {
            overlay.remove();
            document.removeEventListener('keydown', escHandler);
            window.location.href = 'dashboard.html';
        }
    });

    document.getElementById('btnCancelarProyecto').addEventListener('click', function() {
        overlay.remove();
        window.location.href = 'dashboard.html';
    });

    document.getElementById('btnCrearProyecto').addEventListener('click', function() {
        crearProyectoDesdeModal(input.value, overlay);
    });

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.remove();
            window.location.href = 'dashboard.html';
        }
    });
}

/**
 * Crea el proyecto desde el modal
 */
function crearProyectoDesdeModal(nombre, overlay) {
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
            proyectoId = data.id_proyecto;
            window.history.replaceState({}, '', `proyecto.html?id=${proyectoId}`);
            document.getElementById('projectTitle').value = nombre.trim();
            overlay.remove();
            cargarProyecto();
            cargarInventarioUsuario();
            cargarCatalogo();
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
 * Carga los datos del proyecto
 */
function cargarProyecto() {
    fetch(`/ProyectoM/api/proyecto_detalle.php?id=${proyectoId}`, {
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
            document.getElementById('projectTitle').value = data.proyecto.nombre;
            itemsProyecto = data.proyecto.items || [];
            renderizarLista(itemsProyecto);
            actualizarTotales();
        } else if (data && data.error) {
            mostrarError(data.error);
        }
    })
    .catch(error => {
        console.error('Error al cargar proyecto:', error);
        mostrarError('Error al cargar el proyecto');
    });
}

/**
 * Carga el inventario del usuario desde el servidor
 */
function cargarInventarioUsuario() {
    fetch('/ProyectoM/api/inventario_usuario.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.success) {
            inventarioUsuario = {};
            if (data.inventario) {
                data.inventario.forEach(item => {
                    inventarioUsuario[item.id_item] = parseInt(item.cantidad);
                });
            }
            renderizarInventario();
        }
    })
    .catch(error => {
        console.error('Error al cargar inventario:', error);
    });
}

/**
 * Renderiza el inventario del usuario
 */
function renderizarInventario() {
    const container = document.getElementById('inventarioPreview');
    if (!container) return;

    const keys = Object.keys(inventarioUsuario);
    if (keys.length === 0) {
        container.innerHTML = `<span style="color: var(--text-muted); font-size: 12px;">No has agregado materiales a tu inventario</span>`;
        return;
    }

    container.innerHTML = '';
    keys.forEach(itemId => {
        const cantidad = inventarioUsuario[itemId];
        const item = itemsDisponibles.find(i => i.id_item === itemId);
        const nombre = item ? item.nombre : itemId;
        const color = obtenerColorItem(itemId);

        const badge = document.createElement('span');
        badge.className = 'badge badge-grass';
        badge.style.cssText = `
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
        `;
        badge.innerHTML = `
            <span style="display: inline-block; width: 12px; height: 12px; background: ${color}; border-radius: 2px;"></span>
            ${nombre} ×${cantidad}
            <button onclick="quitarDelInventario('${itemId}')" style="background: none; border: none; color: var(--text-red); cursor: pointer; font-size: 14px; padding: 0 2px;">×</button>
        `;
        container.appendChild(badge);
    });
}

/**
 * Agrega un item al inventario del usuario
 */
function agregarAlInventario(itemId, cantidad) {
    const params = new URLSearchParams({
        item_id: itemId,
        cantidad: cantidad
    });

    fetch('/ProyectoM/api/inventario_agregar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: params
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (inventarioUsuario[itemId]) {
                inventarioUsuario[itemId] += cantidad;
            } else {
                inventarioUsuario[itemId] = cantidad;
            }
            renderizarInventario();
            mostrarFeedback('✅ Material agregado al inventario', 'success');
        } else {
            mostrarFeedback('❌ ' + (data.error || 'Error al agregar'), 'error');
        }
    })
    .catch(error => {
        mostrarFeedback('❌ Error al agregar al inventario', 'error');
        console.error(error);
    });
}

/**
 * Quita un item del inventario del usuario
 */
function quitarDelInventario(itemId) {
    const params = new URLSearchParams({
        item_id: itemId
    });

    fetch('/ProyectoM/api/inventario_quitar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: params
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            delete inventarioUsuario[itemId];
            renderizarInventario();
            mostrarFeedback('🗑️ Material eliminado del inventario', 'info');
        }
    })
    .catch(error => console.error(error));
}

/**
 * Limpia todo el inventario del usuario
 */
function limpiarInventario() {
    if (Object.keys(inventarioUsuario).length === 0) return;
    if (!confirm('¿Eliminar todos los materiales de tu inventario?')) return;

    fetch('/ProyectoM/api/inventario_limpiar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({ _method: 'DELETE' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            inventarioUsuario = {};
            renderizarInventario();
            mostrarFeedback('🧹 Inventario limpiado', 'info');
        }
    })
    .catch(error => console.error(error));
}

/**
 * Muestra un modal para agregar materiales al inventario
 */
function mostrarModalAgregarInventario() {
    // Esperar a que los items estén cargados
    if (itemsDisponibles.length === 0) {
        mostrarFeedback('⏳ Cargando catálogo...', 'info');
        return;
    }

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
        min-width: 350px;
        max-width: 90%;
        box-shadow: 0 24px 64px rgba(0,0,0,0.6);
        animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    `;

    // Generar opciones de items
    let optionsHtml = '';
    itemsDisponibles.forEach(item => {
        optionsHtml += `<option value="${item.id_item}">${item.nombre}</option>`;
    });

    modal.innerHTML = `
        <div style="margin-bottom: 24px;">
            <div style="font-family:var(--font-pixel); font-size:8px; color:var(--mc-gold); letter-spacing:1px; margin-bottom:8px;">
                🎒 AGREGAR AL INVENTARIO
            </div>
            <div style="font-size:18px; font-weight:600; color:var(--text);">
                ¿Qué material tienes?
            </div>
            <div style="font-size:13px; color:var(--text-muted); margin-top:4px;">
                Selecciona el material y la cantidad que ya posees
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            <div>
                <label style="display:block; font-size:12px; color:var(--text-secondary); margin-bottom:4px;">Material</label>
                <select id="selectItemInventario" style="
                    width: 100%;
                    padding: 10px 14px;
                    font-size: 14px;
                    background: var(--bg);
                    border: 2px solid var(--border-strong);
                    border-radius: 4px;
                    color: var(--text);
                    outline: none;
                ">
                    ${optionsHtml}
                </select>
            </div>
            <div>
                <label style="display:block; font-size:12px; color:var(--text-secondary); margin-bottom:4px;">Cantidad (unidades)</label>
                <input type="number" id="inputCantidadInventario" value="64" min="1" step="1" style="
                    width: 100%;
                    padding: 10px 14px;
                    font-size: 14px;
                    background: var(--bg);
                    border: 2px solid var(--border-strong);
                    border-radius: 4px;
                    color: var(--text);
                    outline: none;
                ">
            </div>
        </div>

        <div style="margin-top: 24px; display:flex; gap:12px; justify-content:flex-end;">
            <button id="btnCancelarInventario" style="
                padding: 8px 20px;
                background: transparent;
                border: 1px solid var(--border-strong);
                border-radius: 4px;
                color: var(--text-secondary);
                cursor: pointer;
                font-size: 13px;
            ">
                Cancelar
            </button>
            <button id="btnConfirmarInventario" style="
                padding: 8px 24px;
                background: var(--mc-gold);
                border: none;
                border-radius: 4px;
                color: #1a1a2e;
                cursor: pointer;
                font-size: 13px;
                font-weight: 600;
            ">
                ✅ Agregar
            </button>
        </div>
    `;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    setTimeout(() => document.getElementById('inputCantidadInventario').focus(), 100);

    document.getElementById('btnCancelarInventario').addEventListener('click', function() {
        overlay.remove();
    });

    document.getElementById('btnConfirmarInventario').addEventListener('click', function() {
        const itemId = document.getElementById('selectItemInventario').value;
        const cantidad = parseInt(document.getElementById('inputCantidadInventario').value) || 1;
        if (cantidad > 0) {
            agregarAlInventario(itemId, cantidad);
            overlay.remove();
        }
    });

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) overlay.remove();
    });
}

/**
 * Carga el catálogo de items disponibles
 */
function cargarCatalogo() {
    fetch('/ProyectoM/api/items.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.success) {
            itemsDisponibles = data.items;
            renderizarCatalogo(itemsDisponibles);
            // Actualizar inventario con los nombres
            renderizarInventario();
        }
    })
    .catch(error => {
        console.error('Error al cargar catálogo:', error);
    });
}

/**
 * Renderiza el catálogo de bloques
 */
function renderizarCatalogo(items) {
    const grid = document.getElementById('blocksGrid');
    if (!grid) return;

    grid.innerHTML = '';

    items.forEach(item => {
        const div = document.createElement('div');
        div.className = 'block-item';
        div.dataset.id = item.id_item;
        div.dataset.name = item.nombre.toLowerCase();

        const existe = itemsProyecto.some(p => p.id_item === item.id_item);
        if (existe) div.classList.add('selected');

        const color = obtenerColorItem(item.id_item);

        div.innerHTML = `
            <div style="width: 32px; height: 32px; background: ${color}; border-radius: 4px; flex-shrink:0;"></div>
            <span class="block-name">${item.nombre}</span>
            <div style="font-size:10px; color:var(--text-muted);">${item.stack_max || 64}/stack</div>
        `;

        div.addEventListener('click', function() {
            toggleItemProyecto(item.id_item, item.nombre);
        });

        grid.appendChild(div);
    });
}

/**
 * Agrega o quita un item del proyecto
 */
function toggleItemProyecto(itemId, itemNombre) {
    const existe = itemsProyecto.some(p => p.id_item === itemId);

    if (existe) {
        const params = new URLSearchParams({
            proyecto_id: proyectoId,
            item_id: itemId
        });

        fetch('/ProyectoM/api/quitar_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                itemsProyecto = itemsProyecto.filter(p => p.id_item !== itemId);
                renderizarLista(itemsProyecto);
                actualizarTotales();
                document.querySelectorAll('.block-item').forEach(el => {
                    if (el.dataset.id === itemId) el.classList.remove('selected');
                });
                mostrarFeedback('🗑️ Item eliminado', 'info');
            }
        })
        .catch(error => console.error(error));
    } else {
        const cantidad = 64;
        const params = new URLSearchParams({
            proyecto_id: proyectoId,
            item_id: itemId,
            cantidad: cantidad
        });

        fetch('/ProyectoM/api/agregar_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar items del proyecto
                cargarProyecto();
                document.querySelectorAll('.block-item').forEach(el => {
                    if (el.dataset.id === itemId) el.classList.add('selected');
                });
                mostrarFeedback('✅ Item agregado', 'success');
            }
        })
        .catch(error => console.error(error));
    }
}

/**
 * Renderiza la lista de items del proyecto
 */
function renderizarLista(items) {
    const list = document.getElementById('summaryList');
    if (!list) return;

    list.innerHTML = '';

    if (!items || items.length === 0) {
        list.innerHTML = `
            <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">
                <div style="font-size:24px; margin-bottom:8px;">📦</div>
                No hay items en este proyecto<br>
                Haz clic en un bloque del catálogo
            </div>
        `;
        return;
    }

    items.forEach(item => {
        const li = document.createElement('li');
        li.className = 'summary-item';
        li.dataset.id = item.id_item;

        const color = obtenerColorItem(item.id_item);

        li.innerHTML = `
            <div class="summary-item-info">
                <div style="width:20px; height:20px; background:${color}; border-radius:3px; flex-shrink:0;"></div>
                <span class="summary-item-name">${item.nombre}</span>
            </div>
            <div class="qty-controls">
                <button class="qty-btn" onclick="cambiarCantidad('${item.id_item}', -64)">−</button>
                <span class="qty-number">${item.cantidad}</span>
                <button class="qty-btn" onclick="cambiarCantidad('${item.id_item}', 64)">+</button>
            </div>
            <button onclick="quitarItem('${item.id_item}')" class="btn btn-ghost btn-sm" style="padding:0 4px; font-size:16px; color:var(--text-red);">×</button>
        `;

        list.appendChild(li);
    });
}

/**
 * Cambia la cantidad de un item
 */
function cambiarCantidad(itemId, delta) {
    const item = itemsProyecto.find(p => p.id_item === itemId);
    if (!item) return;

    let nuevaCantidad = item.cantidad + delta;
    if (nuevaCantidad <= 0) {
        quitarItem(itemId);
        return;
    }

    const params = new URLSearchParams({
        proyecto_id: proyectoId,
        item_id: itemId,
        cantidad: nuevaCantidad
    });

    fetch('/ProyectoM/api/actualizar_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            item.cantidad = nuevaCantidad;
            renderizarLista(itemsProyecto);
            actualizarTotales();
        }
    })
    .catch(error => console.error(error));
}

/**
 * Quita un item del proyecto
 */
function quitarItem(itemId) {
    const params = new URLSearchParams({
        proyecto_id: proyectoId,
        item_id: itemId
    });

    fetch('/ProyectoM/api/quitar_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            itemsProyecto = itemsProyecto.filter(p => p.id_item !== itemId);
            renderizarLista(itemsProyecto);
            actualizarTotales();
            document.querySelectorAll('.block-item').forEach(el => {
                if (el.dataset.id === itemId) el.classList.remove('selected');
            });
        }
    })
    .catch(error => console.error(error));
}

/**
 * Renombra el proyecto
 */
function renombrarProyecto(nuevoNombre) {
    if (!nuevoNombre || nuevoNombre.trim().length < 3) {
        mostrarFeedback('⚠️ El nombre debe tener al menos 3 caracteres', 'error');
        cargarProyecto();
        return;
    }

    const params = new URLSearchParams({
        proyecto_id: proyectoId,
        nombre: nuevoNombre.trim()
    });

    fetch('/ProyectoM/api/renombrar_proyecto.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarFeedback('✅ Nombre actualizado', 'success');
        } else {
            mostrarFeedback('❌ ' + (data.error || 'Error al renombrar'), 'error');
            cargarProyecto();
        }
    })
    .catch(error => {
        mostrarFeedback('❌ Error al renombrar', 'error');
        cargarProyecto();
    });
}

/**
 * Calcula los materiales del proyecto (restando inventario)
 */
function calcularProyecto() {
    if (itemsProyecto.length === 0) {
        mostrarFeedback('⚠️ Agrega items a la lista primero', 'error');
        return;
    }

    const btn = document.getElementById('btnCalcularProyecto');
    btn.textContent = '⏳ Calculando...';
    btn.disabled = true;

    fetch(`/ProyectoM/api/calcular_proyecto.php?id=${proyectoId}`, {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        btn.textContent = '⬡ Calcular proyecto';
        btn.disabled = false;

        if (data && data.success) {
            // Aplicar descuento del inventario
            const resultadoConDescuento = aplicarDescuentoInventario(data.resultado);
            mostrarResultado(resultadoConDescuento);
        } else if (data && data.error) {
            mostrarFeedback('❌ ' + data.error, 'error');
        }
    })
    .catch(error => {
        btn.textContent = '⬡ Calcular proyecto';
        btn.disabled = false;
        mostrarFeedback('❌ Error al calcular: ' + error.message, 'error');
        console.error(error);
    });
}

/**
 * Aplica el descuento del inventario a los materiales calculados
 */
function aplicarDescuentoInventario(resultado) {
    const materiales = resultado.materiales;
    const stacks = resultado.stacks;
    let tiempoTotal = resultado.tiempo_total;

    // Clonar materiales
    const materialesDescontados = materiales.map(m => ({
        ...m,
        cantidad: m.cantidad,
        original: m.cantidad,
        descontado: 0,
        tieneDescuento: false
    }));

    // Aplicar descuento
    materialesDescontados.forEach(m => {
        const id = m.id_item;
        if (inventarioUsuario[id] && inventarioUsuario[id] > 0) {
            const cantidadInventario = inventarioUsuario[id];
            const descuento = Math.min(cantidadInventario, m.cantidad);
            m.cantidad = Math.max(0, m.cantidad - descuento);
            m.descontado = descuento;
            m.tieneDescuento = true;
        }
    });

    // Recalcular stacks
    const stacksDescontados = {};
    materialesDescontados.forEach(m => {
        const stackMax = m.stack_max || 64;
        const cantidad = m.cantidad;
        stacksDescontados[m.id_item] = {
            id_item: m.id_item,
            cantidad: cantidad,
            stacks: Math.floor(cantidad / stackMax),
            resto: cantidad % stackMax,
            stack_max: stackMax
        };
    });

    // Recalcular tiempo total
    let tiempoDescontado = 0;
    materialesDescontados.forEach(m => {
        if (m.cantidad > 0 && m.original > 0) {
            const proporcion = m.cantidad / m.original;
            tiempoDescontado += (m.tiempo || 0) * proporcion;
        }
    });

    return {
        materiales: materialesDescontados,
        stacks: stacksDescontados,
        tiempo_total: Math.round(tiempoDescontado),
        tieneDescuento: materialesDescontados.some(m => m.tieneDescuento),
        inventarioUsado: Object.keys(inventarioUsuario).length > 0
    };
}

/**
 * Muestra el resultado del cálculo con descuentos aplicados
 */
function mostrarResultado(resultado) {
    const materiales = resultado.materiales;
    const stacks = resultado.stacks;
    const tiempoTotal = resultado.tiempo_total;
    const tieneDescuento = resultado.tieneDescuento;

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
        min-width: 420px;
        max-width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 24px 64px rgba(0,0,0,0.6);
        animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    `;

    // Encabezado con indicador de descuento
    let headerExtra = '';
    if (resultado.inventarioUsado) {
        headerExtra = `
            <div style="background: rgba(247, 201, 72, 0.1); border: 1px solid var(--mc-gold); border-radius: 4px; padding: 8px 12px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">🎒</span>
                <span style="font-size: 12px; color: var(--text);">
                    Se aplicaron descuentos de tu inventario (${Object.keys(inventarioUsuario).length} materiales)
                </span>
            </div>
        `;
    }

    modal.innerHTML = `
        <div style="font-family:var(--font-pixel); font-size:8px; color:var(--mc-gold); letter-spacing:1px; margin-bottom:8px;">
            📊 RESULTADO DEL CÁLCULO
        </div>
        ${headerExtra}
        <div style="margin-bottom: 16px; max-height: 350px; overflow-y: auto; font-size: 13px; line-height: 1.8; font-family: monospace;">
            ${materiales.map(m => {
                const stackInfo = stacks[m.id_item] || {};
                const stacksCompletos = stackInfo.stacks || 0;
                const resto = stackInfo.resto || 0;
                const stackMax = stackInfo.stack_max || 64;
                const descuento = m.descontado || 0;
                const tieneDesc = m.tieneDescuento && descuento > 0;
                
                let texto = `📦 ${m.nombre}\n`;
                texto += `   Total: ${formatearNumero(m.cantidad)} unidades`;
                if (tieneDesc) {
                    texto += ` (${formatearNumero(descuento)} de tu inventario)`;
                }
                texto += `\n`;
                if (stacksCompletos > 0 || resto > 0) {
                    texto += `   → ${stacksCompletos} stacks + ${resto} unidades (${stackMax}/stack)\n`;
                }
                if (m.cantidad === 0 && tieneDesc) {
                    texto += `   ✅ ¡Ya tienes suficientes materiales!\n`;
                }
                return texto;
            }).join('\n')}
        </div>
        <div style="border-top:1px solid var(--border); padding-top:16px; margin-top:8px;">
            <div style="display:flex; justify-content:space-between; font-size:13px;">
                <span style="color:var(--text-muted);">⏱️ Tiempo total de horneado:</span>
                <span style="color:var(--mc-gold); font-weight:600;">${tiempoTotal} segundos</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:13px; margin-top:4px;">
                <span style="color:var(--text-muted);">📦 Total de stacks necesarios:</span>
                <span style="font-weight:600;">${Object.values(stacks).reduce((sum, s) => sum + s.stacks, 0)} stacks</span>
            </div>
            ${resultado.inventarioUsado ? `
            <div style="display:flex; justify-content:space-between; font-size:13px; margin-top:4px; color: var(--mc-gold);">
                <span>🎒 Materiales descontados:</span>
                <span>${Object.keys(inventarioUsuario).length} tipos</span>
            </div>
            ` : ''}
        </div>
        <div style="margin-top:24px; display:flex; gap:12px; justify-content:flex-end;">
            <button onclick="this.closest('div[style]').parentElement.remove()" style="
                padding: 8px 24px;
                background: var(--mc-diamond);
                border: none;
                border-radius: 4px;
                color: #1a1a2e;
                cursor: pointer;
                font-size: 13px;
                font-weight: 600;
            ">
                ✅ Cerrar
            </button>
        </div>
    `;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) overlay.remove();
    });
}

/**
 * Limpia la lista de items del proyecto
 */
function limpiarLista() {
    if (itemsProyecto.length === 0) return;
    if (!confirm('¿Eliminar todos los items de este proyecto?')) return;

    const promises = itemsProyecto.map(item => {
        const params = new URLSearchParams({
            proyecto_id: proyectoId,
            item_id: item.id_item
        });
        return fetch('/ProyectoM/api/quitar_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params
        });
    });

    Promise.all(promises)
        .then(() => {
            itemsProyecto = [];
            renderizarLista(itemsProyecto);
            actualizarTotales();
            document.querySelectorAll('.block-item').forEach(el => el.classList.remove('selected'));
            mostrarFeedback('🧹 Lista limpiada', 'info');
        })
        .catch(error => console.error(error));
}

/**
 * Actualiza los totales en el footer
 */
function actualizarTotales() {
    let total = 0;
    itemsProyecto.forEach(item => {
        total += item.cantidad || 0;
    });

    document.getElementById('totalUnits').textContent = formatearNumero(total);
    document.getElementById('totalStacks').textContent = Math.floor(total / 64);

    const count = itemsProyecto.length;
    document.getElementById('countBadge').textContent = count + (count === 1 ? ' item' : ' items');
}

/**
 * Filtra el catálogo por búsqueda
 */
function filtrarBloques(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('.block-item').forEach(el => {
        const name = el.dataset.name || '';
        el.style.display = name.includes(q) ? '' : 'none';
    });
}

/**
 * Obtiene un color según el ID del item
 */
function obtenerColorItem(itemId) {
    const colores = {
        'iron_ore': '#C8A87C',
        'raw_iron': '#D4C4B0',
        'iron_nugget': '#D4C4B0',
        'iron_ingot': '#D4C4B0',
        'iron_block': '#C8C8C8',
        'cobblestone': '#6B6B6B',
        'stone': '#7D7D7D',
        'furnace': '#6B6B6B',
        'default': '#4A4A4A'
    };
    return colores[itemId] || colores['default'];
}

/**
 * Formatea un número con separadores
 */
function formatearNumero(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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
        background: ${tipo === 'success' ? '#2d5016' : tipo === 'error' ? '#441111' : '#1a2a3a'};
        color: ${tipo === 'success' ? '#8bc34a' : tipo === 'error' ? '#ff6b6b' : '#8bc34a'};
        border: 1px solid ${tipo === 'success' ? '#4a7a2a' : tipo === 'error' ? '#662222' : '#2a4a5a'};
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
 * Mostrar error (usa feedback)
 */
function mostrarError(mensaje) {
    mostrarFeedback('❌ ' + mensaje, 'error');
}