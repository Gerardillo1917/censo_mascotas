<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/database/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

// Forzar autenticación
requerir_autenticacion();
$page_title = "Agregar Tutor";
include(BASE_PATH . '/includes/header.php');
?>

<div class="container">
    <h2 class="mb-4" style="color: #8b0180;">Registro de Tutor y Mascota</h2>
    
    <form action="<?php echo BASE_URL; ?>/guardar_tutor.php" method="post" enctype="multipart/form-data" id="form-tutor">
        <!-- Sección del Tutor -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-purple text-white">
                <h5 class="mb-0" style="color: #8b0180;">Datos del Tutor</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Nombre(s)*</label>
                        <input type="text" class="form-control" name="nombre" required pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+" title="Solo letras y espacios">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Apellido Paterno*</label>
                        <input type="text" class="form-control" name="apellido_paterno" required pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+" title="Solo letras y espacios">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Apellido Materno</label>
                        <input type="text" class="form-control" name="apellido_materno" pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]*" title="Solo letras y espacios">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Teléfono*</label>
                        <input type="tel" class="form-control" name="telefono" required pattern="[0-9]{10}" title="10 dígitos numéricos" maxlength="10">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Edad</label>
                        <input type="number" class="form-control" name="edad" min="18" max="120">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" name="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Formato de correo válido: usuario@dominio.com">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Calle*</label>
                        <input type="text" class="form-control" name="calle" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Núm Ext*</label>
                        <input type="text" class="form-control" name="numero_exterior" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Núm Int</label>
                        <input type="text" class="form-control" name="numero_interior">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Código Postal*</label>
                        <input type="text" class="form-control" name="codigo_postal" required pattern="72[0-9]{3}" title="Código postal de Puebla (72XXX)" maxlength="5">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Colonia*</label>
                        <select class="form-select" name="colonia" id="colonia" required>
                            <option value="">Seleccione...</option>
                            <option value="San Bernardino Tlaxcalancingo">San Bernardino Tlaxcalancingo</option>
                            <option value="San Andrés Cholula">San Andrés Cholula</option>
                            <option value="San Luis Tehuiloyocan">San Luis Tehuiloyocan</option>
                            <option value="San Rafael Comac">San Rafael Comac</option>
                            <option value="Buenavista">Buenavista</option>
                            <option value="Santa María Tonantzintla">Santa María Tonantzintla</option>
                            <option value="San Pedro Tonantzintla">San Pedro Tonantzintla</option>
                            <option value="Tonantzintla">Tonantzintla</option>
                            <option value="Otro">Otra colonia</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="otra_colonia_container" style="display:none;">
                        <label class="form-label">Especificar colonia*</label>
                        <input type="text" class="form-control" name="otra_colonia" id="otra_colonia">
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex flex-column align-items-center">
                            <span class="text-muted">Foto del tutor</span>
                        </div>
                        <input type="file" id="foto-tutor" name="foto_tutor" accept="image/*" class="form-control form-control-sm">
                        <small class="text-muted">formatos: jpg, png (máx. 2mb)</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sección de la Mascota -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-purple text-white">
                <h5 class="mb-0" style="color: #8b0180;">Datos de la Mascota</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Nombre*</label>
                        <input type="text" class="form-control" name="nombre_mascota" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Especie*</label>
                        <select class="form-select" name="especie" id="especie" required>
                            <option value="">Seleccione...</option>
                            <option value="Perro">Perro</option>
                            <option value="Gato">Gato</option>
                            <option value="Ave">Ave</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="otra_especie_container" style="display:none;">
                        <label class="form-label">Especificar especie*</label>
                        <input type="text" class="form-control" name="otra_especie" id="otra_especie">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Edad (años)*</label>
                        <input type="number" class="form-control" name="edad_mascota" min="0" max="600" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Género*</label>
                        <select class="form-select" name="genero" required>
                            <option value="">Seleccione...</option>
                            <option value="Macho">Macho</option>
                            <option value="Hembra">Hembra</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Raza</label>
                        <input type="text" class="form-control" name="raza">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" name="color">
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-3 pt-1">
                            <input class="form-check-input" type="checkbox" name="esterilizado" id="esterilizado">
                            <label class="form-check-label" for="esterilizado">Esterilizado</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex flex-column align-items-center">
                            <span class="text-muted">Foto de la mascota</span>
                        </div>
                        <input type="file" id="foto-mascota" name="foto_mascota" accept="image/*" class="form-control form-control-sm" required>
                        <small class="text-muted">formatos: jpg, png (máx. 2mb)</small>
                    </div>
                    <div class="col-md-12 mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="tiene_vacuna" id="tiene_vacuna">
                            <label class="form-check-label" for="tiene_vacuna">Tiene vacunas</label>
                        </div>
                        <small class="text-muted">Si marca esta opción, puede agregar una o más vacunas a continuación.</small>
                        <div id="vacuna_fields" class="mt-2" style="display:none;">
                            <div class="vacunas-container">
                                <!-- Vacunas se agregarán aquí dinámicamente -->
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-vacuna">
                                <i class="bi bi-plus"></i> Agregar vacuna
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="incapacidad" id="incapacidad">
                            <label class="form-check-label" for="incapacidad">Tiene discapacidad</label>
                        </div>
                        <div id="incapacidad_fields" class="mt-2" style="display:none;">
                            <label class="form-label small">Descripción incapacidad</label>
                            <textarea class="form-control" name="descripcion_incapacidad" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <label class="form-label">Comentarios adicionales</label>
                        <textarea class="form-control" name="comentarios_mascota" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Script para controles interactivos -->
        <script>
        // Mostrar/ocultar campo de otra especie
        document.getElementById('especie').addEventListener('change', function() {
            const otraEspecieContainer = document.getElementById('otra_especie_container');
            otraEspecieContainer.style.display = this.value === 'Otro' ? 'block' : 'none';
            if (this.value !== 'Otro') {
                document.getElementById('otra_especie').value = '';
            }
        });

        // Mostrar/ocultar campo de otra colonia
        document.getElementById('colonia').addEventListener('change', function() {
            const otraColoniaContainer = document.getElementById('otra_colonia_container');
            otraColoniaContainer.style.display = this.value === 'Otro' ? 'block' : 'none';
            if (this.value !== 'Otro') {
                document.getElementById('otra_colonia').value = '';
            }
        });

        // Validar colonia antes de enviar
        document.getElementById('form-tutor').addEventListener('submit', function(e) {
            const coloniaSelect = document.getElementById('colonia');
            if (coloniaSelect.value === 'Otro' && document.getElementById('otra_colonia').value.trim() === '') {
                e.preventDefault();
                alert('Por favor especifique la colonia');
                return false;
            }
            return true;
        });

        // Mostrar/ocultar campos de vacunas
        document.getElementById('tiene_vacuna').addEventListener('change', function() {
            document.getElementById('vacuna_fields').style.display = this.checked ? 'block' : 'none';
        });

        // Mostrar/ocultar campos de incapacidad
        document.getElementById('incapacidad').addEventListener('change', function() {
            document.getElementById('incapacidad_fields').style.display = this.checked ? 'block' : 'none';
        });

        // Plantilla para vacunas
        const vacunaTemplate = (index = '') => `
            <div class="vacuna-item mb-3 p-3 border rounded">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Vacuna ${index}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-vacuna">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small">Nombre vacuna*</label>
                        <input type="text" class="form-control" name="vacunas[nombre][]" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Fecha aplicación*</label>
                        <input type="date" class="form-control" name="vacunas[fecha][]" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small">Comentarios</label>
                        <input type="text" class="form-control" name="vacunas[comentarios][]">
                    </div>
                </div>
            </div>
        `;

        // Agregar primera vacuna
        document.getElementById('add-vacuna').addEventListener('click', function() {
            const container = document.querySelector('.vacunas-container');
            const count = container.children.length + 1;
            const newVacuna = document.createElement('div');
            newVacuna.innerHTML = vacunaTemplate(count);
            container.appendChild(newVacuna);
            
            // Agregar evento al botón de eliminar
            newVacuna.querySelector('.remove-vacuna').addEventListener('click', function() {
                this.closest('.vacuna-item').remove();
            });
        });

        // Eliminar vacunas
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-vacuna')) {
                e.target.closest('.vacuna-item').remove();
            }
        });
        </script>
        
        <!-- Botones centrados y con estilo -->
        <div class="d-flex justify-content-center gap-2 mt-3">
            <button type="submit" class="btn btn-guardar">Guardar Registro</button>
            <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-cancelar">Cancelar</a>
        </div>

        <!-- Estilos personalizados -->
        <style>
            :root {
                --purple-primary: #8b0180;
                --red-cancel: #dc3545;
                --white-text: #ffffff;
                --border-color: #dee2e6;
            }

            .text-purple {
                color: var(--purple-primary);
            }

            .card-purple-border {
                border: 1px solid var(--purple-primary);
            }

            .card-header.bg-purple {
                background-color: var(--purple-primary) !important;
                color: var(--white-text) !important;
            }

            .card-header.bg-purple h5 {
                color: var(--white-text) !important;
            }

            .btn-purple-primary {
                background-color: var(--purple-primary);
                border-color: var(--purple-primary);
                color: var(--white-text);
            }

            .btn-purple-primary:hover {
                background-color: #6a0160;
                border-color: #6a0160;
                color: var(--white-text);
            }

            .btn-red-cancel {
                background-color: var(--red-cancel);
                border-color: var(--red-cancel);
                color: var(--white-text);
            }

            .btn-red-cancel:hover {
                background-color: #c82333;
                border-color: #bd2130;
                color: var(--white-text);
            }

            .btn-guardar {
                background-color: #8b0180;
                color: #fff;
                border: none;
                padding: 0.5rem 1.2rem;
                border-radius: 5px;
                transition: background-color 0.3s ease;
            }

            .btn-guardar:hover {
                background-color: #6a0160;
            }

            .btn-cancelar {
                background-color: #dc3545;
                color: #fff;
                border: none;
                padding: 0.5rem 1.2rem;
                border-radius: 5px;
                transition: background-color 0.3s ease;
                text-decoration: none;
                display: inline-block;
            }

            .btn-cancelar:hover {
                background-color: #b02a37;
            }
        </style>
        <script>
// Capitalizar automáticamente nombres, apellidos y colonia
document.addEventListener('DOMContentLoaded', function() {
    const camposCapitalizar = ['nombre', 'apellido_paterno', 'apellido_materno', 'otra_colonia'];
    
    camposCapitalizar.forEach(campo => {
        const input = document.querySelector(`[name="${campo}"]`);
        if (input) {
            input.addEventListener('blur', function() {
                if (this.value) {
                    this.value = this.value.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
                }
            });
        }
    });

    // También capitalizar al enviar el formulario por si acaso
    document.getElementById('form-tutor').addEventListener('submit', function() {
        camposCapitalizar.forEach(campo => {
            const input = document.querySelector(`[name="${campo}"]`);
            if (input && input.value) {
                input.value = input.value.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
            }
        });
    });
});
</script>
    </form>
</div>

<?php include(BASE_PATH . '/includes/footer.php'); ?>