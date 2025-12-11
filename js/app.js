document.addEventListener('DOMContentLoaded', function() {
    loadEmbarques();
    loadContenedores();
    loadRutas();
    loadDocumentacion();
    renderFormularios();
});

document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
    tab.addEventListener('shown.bs.tab', function(e) {
        const target = e.target.getAttribute('data-bs-target');
        
        if (target === '#embarques') {
            loadEmbarques();
        } else if (target === '#contenedores') {
            loadContenedores();
        } else if (target === '#rutas') {
            loadRutas();
        } else if (target === '#documentacion') {
            loadDocumentacion();
        } else if (target === '#agregar') {
            renderFormularios();
        }
    });
});

function loadEmbarques() {
    fetch('api/embarques.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('embarques-list');
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div class="empty-state" style="grid-column: 1/-1;">
                        <p>No hay embarques disponibles</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = data.map(embarque => `
                <div class="card-custom">
                    <div class="card-header">
                        <h5 class="mb-0">${embarque.nombre}</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Barco:</strong> ${embarque.barco}</p>
                        <p class="mb-2">
                            <strong>Estado:</strong> 
                            <span class="badge-custom badge-${embarque.estado}">${embarque.estado}</span>
                        </p>
                        <p class="mb-2"><strong>Salida:</strong> ${embarque.fecha_salida}</p>
                        <p class="mb-2"><strong>Llegada:</strong> ${embarque.fecha_llegada}</p>
                        <p class="mb-2"><strong>Ruta:</strong> ${embarque.ruta_nombre || 'N/A'}</p>
                        <p class="mb-3"><strong>Contenedores:</strong> ${embarque.contenedores_count}</p>
                        <div class="mb-3">
                            ${embarque.contenedores.map(c => `
                                <span class="badge bg-secondary">${c.numero}</span>
                            `).join('')}
                        </div>
                        <button class="btn btn-sm btn-danger-custom" onclick="deleteEmbarque(${embarque.id})">
                            Eliminar
                        </button>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => console.error('Error:', error));
}

function deleteEmbarque(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este embarque?')) {
        fetch('api/embarques.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Embarque eliminado exitosamente');
                loadEmbarques();
            } else {
                alert('Error al eliminar: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function loadContenedores() {
    fetch('api/contenedores.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('contenedores-list');
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div class="empty-state" style="grid-column: 1/-1;">
                        <p>No hay contenedores disponibles</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = data.map(contenedor => `
                <div class="card-custom">
                    <div class="card-header">
                        <h5 class="mb-0">${contenedor.numero}</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Tipo:</strong> ${contenedor.tipo}</p>
                        <p class="mb-2"><strong>Capacidad:</strong> ${contenedor.capacidad} toneladas</p>
                        <p class="mb-2"><strong>Contenido:</strong> ${contenedor.contenido}</p>
                        <p class="mb-3"><strong>Viajes:</strong> ${contenedor.embarques_count}</p>
                        <div class="mb-3">
                            ${contenedor.embarques_count > 0 ? 
                                contenedor.embarques.map(e => `
                                    <span class="badge bg-secondary">${e.nombre}</span>
                                `).join('') :
                                '<span class="text-muted">Sin asignar</span>'
                            }
                        </div>
                        <button class="btn btn-sm btn-danger-custom" onclick="deleteContenedor(${contenedor.id})">
                            Eliminar
                        </button>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => console.error('Error:', error));
}

function deleteContenedor(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este contenedor?')) {
        fetch('api/contenedores.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Contenedor eliminado exitosamente');
                loadContenedores();
            } else {
                alert('Error al eliminar: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function loadRutas() {
    fetch('api/rutas.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('rutas-list');
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>No hay rutas disponibles</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = data.map(ruta => `
                <div class="route-item">
                    <h5><strong>${ruta.nombre}</strong></h5>
                    <p class="mb-2"><strong>Origen:</strong> ${ruta.origen}</p>
                    <p class="mb-2"><strong>Destino:</strong> ${ruta.destino}</p>
                    <p class="mb-3"><strong>Duración total:</strong> ${ruta.dias_totales} días</p>
                    <div class="escalas-container">
                        <strong>Escalas (${ruta.escalas.length}):</strong>
                        ${ruta.escalas.map((escala, idx) => `
                            <div class="escala-item">
                                <strong>${idx + 1}.</strong> ${escala.puerto} - ${escala.dias} días
                            </div>
                        `).join('')}
                    </div>
                    <button class="btn btn-sm btn-danger-custom mt-3" onclick="deleteRuta(${ruta.id})">
                        Eliminar
                    </button>
                </div>
            `).join('');
        })
        .catch(error => console.error('Error:', error));
}

function deleteRuta(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta ruta?')) {
        fetch('api/rutas.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Ruta eliminada exitosamente');
                loadRutas();
            } else {
                alert('Error al eliminar: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function loadDocumentacion() {
    fetch('api/documentos.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('documentacion-list');
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>No hay documentos disponibles</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = data.map(doc => `
                <div class="doc-item">
                    <strong>${getTipoDocumento(doc.tipo)} - ${doc.numero}</strong>
                    <p><strong>Embarque:</strong> ${doc.embarque_nombre}</p>
                    <p><strong>Contenedor:</strong> ${doc.contenedor_numero || 'N/A'}</p>
                    <p><strong>Descripción:</strong> ${doc.descripcion}</p>
                    <p><strong>Fecha de emisión:</strong> ${doc.fecha_emision}</p>
                    <button class="btn btn-sm btn-danger-custom mt-2" onclick="deleteDocumento(${doc.id})">
                        Eliminar
                    </button>
                </div>
            `).join('');
        })
        .catch(error => console.error('Error:', error));
}

function deleteDocumento(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este documento?')) {
        fetch('api/documentos.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Documento eliminado exitosamente');
                loadDocumentacion();
            } else {
                alert('Error al eliminar: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function getTipoDocumento(tipo) {
    const tipos = {
        'BL': 'Bill of Lading',
        'Invoice': 'Factura',
        'Packing List': 'Lista de Empaque',
        'Certificate': 'Certificado'
    };
    return tipos[tipo] || tipo;
}

function renderFormularios() {
    const container = document.getElementById('form-container');
    
    Promise.all([
        fetch('api/rutas.php').then(r => r.json()),
        fetch('api/embarques.php').then(r => r.json()),
        fetch('api/contenedores.php').then(r => r.json())
    ])
    .then(([rutas, embarques, contenedores]) => {
        container.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-section">
                        <h4>Nuevo Embarque</h4>
                        <form onsubmit="submitEmbarque(event)">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Embarque</label>
                                <input type="text" class="form-control" id="emb-nombre" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nombre del Barco</label>
                                <input type="text" class="form-control" id="emb-barco" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Estado</label>
                                <select class="form-control" id="emb-estado" required>
                                    <option value="activo">Activo</option>
                                    <option value="en-transito">En Tránsito</option>
                                    <option value="completado">Completado</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fecha de Salida</label>
                                <input type="date" class="form-control" id="emb-salida" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fecha de Llegada</label>
                                <input type="date" class="form-control" id="emb-llegada" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ruta</label>
                                <select class="form-control" id="emb-ruta" required>
                                    <option value="">Seleccionar ruta</option>
                                    ${rutas.map(r => `<option value="${r.id}">${r.nombre}</option>`).join('')}
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary-custom w-100">
                                Agregar Embarque
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-section">
                        <h4>Nuevo Contenedor</h4>
                        <form onsubmit="submitContenedor(event)">
                            <div class="mb-3">
                                <label class="form-label">Número de Contenedor</label>
                                <input type="text" class="form-control" id="cont-numero" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipo</label>
                                <select class="form-control" id="cont-tipo" required>
                                    <option value="20ft">20ft</option>
                                    <option value="40ft">40ft</option>
                                    <option value="HC">HC</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Capacidad (toneladas)</label>
                                <input type="number" step="0.01" class="form-control" id="cont-capacidad" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contenido</label>
                                <input type="text" class="form-control" id="cont-contenido" required>
                            </div>
                            <button type="submit" class="btn btn-primary-custom w-100">
                                Agregar Contenedor
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-section">
                        <h4>Nueva Ruta</h4>
                        <form onsubmit="submitRuta(event)">
                            <div class="mb-3">
                                <label class="form-label">Nombre de la Ruta</label>
                                <input type="text" class="form-control" id="ruta-nombre" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Puerto de Origen</label>
                                <input type="text" class="form-control" id="ruta-origen" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Puerto de Destino</label>
                                <input type="text" class="form-control" id="ruta-destino" required>
                            </div>
                            <button type="submit" class="btn btn-primary-custom w-100">
                                Agregar Ruta
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-section">
                        <h4>Nuevo Documento</h4>
                        <form onsubmit="submitDocumento(event)">
                            <div class="mb-3">
                                <label class="form-label">Tipo de Documento</label>
                                <select class="form-control" id="doc-tipo" required>
                                    <option value="BL">Bill of Lading</option>
                                    <option value="Invoice">Factura</option>
                                    <option value="Packing List">Lista de Empaque</option>
                                    <option value="Certificate">Certificado</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Número de Documento</label>
                                <input type="text" class="form-control" id="doc-numero" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Embarque</label>
                                <select class="form-control" id="doc-embarque" required>
                                    <option value="">Seleccionar embarque</option>
                                    ${embarques.map(e => `<option value="${e.id}">${e.nombre}</option>`).join('')}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contenedor</label>
                                <select class="form-control" id="doc-contenedor">
                                    <option value="">Seleccionar contenedor (opcional)</option>
                                    ${contenedores.map(c => `<option value="${c.id}">${c.numero}</option>`).join('')}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" id="doc-descripcion" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fecha de Emisión</label>
                                <input type="date" class="form-control" id="doc-fecha" required>
                            </div>
                            <button type="submit" class="btn btn-primary-custom w-100">
                                Agregar Documento
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        `;
    })
    .catch(error => console.error('Error:', error));
}

function submitEmbarque(event) {
    event.preventDefault();
    
    const data = {
        nombre: document.getElementById('emb-nombre').value,
        barco: document.getElementById('emb-barco').value,
        estado: document.getElementById('emb-estado').value,
        fecha_salida: document.getElementById('emb-salida').value,
        fecha_llegada: document.getElementById('emb-llegada').value,
        ruta_id: document.getElementById('emb-ruta').value
    };
    
    fetch('api/embarques.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Embarque agregado exitosamente');
            event.target.reset();
            renderFormularios();
            loadEmbarques();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function submitContenedor(event) {
    event.preventDefault();
    
    const data = {
        numero: document.getElementById('cont-numero').value,
        tipo: document.getElementById('cont-tipo').value,
        capacidad: document.getElementById('cont-capacidad').value,
        contenido: document.getElementById('cont-contenido').value
    };
    
    fetch('api/contenedores.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Contenedor agregado exitosamente');
            event.target.reset();
            renderFormularios();
            loadContenedores();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function submitRuta(event) {
    event.preventDefault();
    
    const data = {
        nombre: document.getElementById('ruta-nombre').value,
        origen: document.getElementById('ruta-origen').value,
        destino: document.getElementById('ruta-destino').value
    };
    
    fetch('api/rutas.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Ruta agregada exitosamente');
            event.target.reset();
            renderFormularios();
            loadRutas();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function submitDocumento(event) {
    event.preventDefault();
    
    const data = {
        tipo: document.getElementById('doc-tipo').value,
        numero: document.getElementById('doc-numero').value,
        embarque_id: document.getElementById('doc-embarque').value,
        contenedor_id: document.getElementById('doc-contenedor').value || null,
        descripcion: document.getElementById('doc-descripcion').value,
        fecha_emision: document.getElementById('doc-fecha').value
    };
    
    fetch('api/documentos.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Documento agregado exitosamente');
            event.target.reset();
            renderFormularios();
            loadDocumentacion();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}
