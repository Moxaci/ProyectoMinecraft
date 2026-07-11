/**
 * CraftLog - Módulo de Proyecto
 * Maneja la carga, edición y cálculo de proyectos
 */

let proyectoId = null;
let itemsDisponibles = [];
let itemsProyecto = [];

document.addEventListener('DOMContentLoaded', function() {
    // Obtener ID del proyecto de la URL
    const urlParams = new URLSearchParams(window.location.search);
    proyectoId = urlParams.get('id') || 0;

    if (proyectoId === 0 || proyectoId === '0') {
        // Si no hay ID, crear un nuevo proyecto automáticamente
        crearNuevoProyecto();
    } else {
        // Cargar proyecto existente
        cargarProyecto();
    }

    // Configurar búsqueda de bloques
    const searchInput = document.getElementById('searchBlocks');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filtrarBloques(this.value);
        });
    }

    // Configurar renombre del proyecto (Enter para guardar)
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

    // Configurar botón calcular
    document.getElementById('btnCalcular').addEventListener('click', function() {
        calcularProyecto();
    });

    // Configurar botón limpiar
    document.getElementById('btnLimpiar').addEventListener('click', function() {
        limpiarLista();
    });

    // Cargar catálogo de items
    cargarCatalogo();
});

/**
 * Crea un nuevo proyecto automáticamente
 */
function crearNuevoProyecto() {
    const nombre = prompt('📝 Nombre del nuevo proyecto:');
    if (!nombre || nombre.trim() === '') {
        window.location.href = 'dashboard.html';
        return;
    }

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
            // Actualizar URL sin recargar
            window.history.replaceState({}, '', `proyecto.html?id=${proyectoId}`);
            // Actualizar título
            document.getElementById('projectTitle').value = nombre.trim();
            // Cargar el proyecto
            cargarProyecto();
            mostrarFeedback('✅ Proyecto creado exitosamente', 'success');
        } else {
            alert('❌ Error: ' + (data.error || 'No se pudo crear el proyecto'));
            window.location.href = 'dashboard.html';
        }
    })
    .catch(error => {
        alert('❌ Error al crear el proyecto');
        console.error(error);
        window.location.href = 'dashboard.html';
    });
}

/**
 * Carga los datos del proyecto
 */
function cargarProyecto() {
    // Cargar detalles del proyecto
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
            // Actualizar título
            document.getElementById('projectTitle').value = data.proyecto.nombre;
            // Guardar items
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

        // Verificar si ya está en el proyecto
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
        // Quitar item
        const params = new URLSearchParams({
            proyecto_id: proyectoId,
            item_id: itemId
        });

        fetch('/ProyectoM/api/quitar_item.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                itemsProyecto = itemsProyecto.filter(p => p.id_item !== itemId);
                renderizarLista(itemsProyecto);
                actualizarTotales();
                // Actualizar catálogo
                document.querySelectorAll('.block-item').forEach(el => {
                    if (el.dataset.id === itemId) el.classList.remove('selected');
                });
                mostrarFeedback('🗑️ Item eliminado', 'info');
            }
        })
        .catch(error => console.error(error));
    } else {
        // Agregar item (por defecto 64)
        const cantidad = 64;
        const params = new URLSearchParams({
            proyecto_id: proyectoId,
            item_id: itemId,
            cantidad: cantidad
        });

        fetch('/ProyectoM/api/agregar_item.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar items del proyecto
                cargarProyecto();
                // Actualizar catálogo
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
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
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
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: params
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            itemsProyecto = itemsProyecto.filter(p => p.id_item !== itemId);
            renderizarLista(itemsProyecto);
            actualizarTotales();
            // Actualizar catálogo
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
        cargarProyecto(); // Recargar nombre original
        return;
    }

    const params = new URLSearchParams({
        proyecto_id: proyectoId,
        nombre: nuevoNombre.trim()
    });

    fetch('/ProyectoM/api/renombrar_proyecto.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: params
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarFeedback('✅ Nombre actualizado', 'success');
        } else {
            mostrarFeedback('❌ ' + (data.error || 'Error al renombrar'), 'error');
            cargarProyecto(); // Recargar nombre original
        }
    })
    .catch(error => {
        mostrarFeedback('❌ Error al renombrar', 'error');
        cargarProyecto();
    });
}

/**
 * Calcula los materiales del proyecto
 */
function calcularProyecto() {
    if (itemsProyecto.length === 0) {
        mostrarFeedback('⚠️ Agrega items a la lista primero', 'error');
        return;
    }

    document.getElementById('btnCalcular').textContent = '⏳ Calculando...';
    document.getElementById('btnCalcular').disabled = true;

    fetch(`/ProyectoM/api/calcular_proyecto.php?id=${proyectoId}`, {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('btnCalcular').textContent = '⬡ Calcular';
        document.getElementById('btnCalcular').disabled = false;

        if (data && data.success) {
            mostrarResultado(data.resultado);
        } else if (data && data.error) {
            mostrarFeedback('❌ ' + data.error, 'error');
        }
    })
    .catch(error => {
        document.getElementById('btnCalcular').textContent = '⬡ Calcular';
        document.getElementById('btnCalcular').disabled = false;
        mostrarFeedback('❌ Error al calcular', 'error');
        console.error(error);
    });
}

/**
 * Muestra el resultado del cálculo
 */
function mostrarResultado(resultado) {
    const materiales = resultado.materiales;
    const stacks = resultado.stacks;
    const tiempoTotal = resultado.tiempo_total;

    let mensaje = '📊 RESULTADO DEL CÁLCULO\n\n';
    mensaje += '═══════════════════════════════\n\n';

    materiales.forEach(m => {
        const stackInfo = stacks[m.id_item] || {};
        const stacksCompletos = stackInfo.stacks || 0;
        const resto = stackInfo.resto || 0;
        const stackMax = stackInfo.stack_max || 64;

        mensaje += `📦 ${m.nombre}\n`;
        mensaje += `   Total: ${formatearNumero(m.cantidad)} unidades\n`;
        if (stacksCompletos > 0 || resto > 0) {
            mensaje += `   → ${stacksCompletos} stacks + ${resto} unidades (${stackMax}/stack)\n`;
        }
        mensaje += '\n';
    });

    mensaje += '═══════════════════════════════\n';
    mensaje += `⏱️ Tiempo total de horneado: ${tiempoTotal} segundos\n`;
    mensaje += `   → ${Math.floor(tiempoTotal / 3600)} horas, ${Math.floor((tiempoTotal % 3600) / 60)} minutos\n`;

    // Crear un modal para mostrar el resultado
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
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 24px 64px rgba(0,0,0,0.6);
        animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    `;

    modal.innerHTML = `
        <div style="font-family:var(--font-pixel); font-size:8px; color:var(--mc-gold); letter-spacing:1px; margin-bottom:8px;">
            📊 RESULTADO DEL CÁLCULO
        </div>
        <div style="margin-bottom:24px; max-height:400px; overflow-y:auto; font-size:13px; line-height:1.8; white-space:pre-wrap; font-family:monospace;">
            ${materiales.map(m => {
                const stackInfo = stacks[m.id_item] || {};
                const stacksCompletos = stackInfo.stacks || 0;
                const resto = stackInfo.resto || 0;
                const stackMax = stackInfo.stack_max || 64;
                let texto = `📦 ${m.nombre}\n`;
                texto += `   Total: ${formatearNumero(m.cantidad)} unidades\n`;
                if (stacksCompletos > 0 || resto > 0) {
                    texto += `   → ${stacksCompletos} stacks + ${resto} unidades (${stackMax}/stack)\n`;
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
                <span style="color:var(--text-muted);">📦 Total de stacks:</span>
                <span style="font-weight:600;">${Object.values(stacks).reduce((sum, s) => sum + s.stacks, 0)} stacks</span>
            </div>
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

    // Eliminar todos los items uno por uno
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
    // Reutilizar la misma función que en dashboard.js
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