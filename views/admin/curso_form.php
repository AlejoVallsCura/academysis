<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-calendar3 me-2"></i><?= $isNew ? 'Nuevo Curso' : 'Editar Curso' ?>
    </h4>
    <a href="index.php?controller=admin&action=cursos" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width:680px;">
    <div class="card-body">
        <form method="post" action="index.php?controller=admin&action=curso">
            <?php if ($isNew): ?>
                <input type="hidden" name="_isNew" value="1">
            <?php else: ?>
                <input type="hidden" name="IDCurso" value="<?= (int)$curso['IDCurso'] ?>">
            <?php endif; ?>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Año Lectivo</label>
                    <input type="number" name="AnioLectivo" class="form-control" required min="2000" max="2100"
                           value="<?= htmlspecialchars($curso['AnioLectivo'] ?? date('Y')) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Materia</label>
                <select name="CodMateria" class="form-select" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($materias as $mat): ?>
                        <option value="<?= htmlspecialchars($mat['CodMateria']) ?>"
                            <?= ($curso['CodMateria'] ?? '') === $mat['CodMateria'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($mat['NomMateria']) ?>
                            <?php if ($mat['CodCarrera']): ?>
                                (<?= htmlspecialchars($mat['CodCarrera']) ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Docente</label>
                <select name="Legajo" class="form-select" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($docentes as $doc): ?>
                        <option value="<?= htmlspecialchars($doc['Legajo']) ?>"
                            <?= ($curso['Legajo'] ?? '') == $doc['Legajo'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($doc['Apellido'] . ', ' . $doc['Nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Aula</label>
                <select name="IDAula" class="form-select" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($aulas as $aula): ?>
                        <option value="<?= (int)$aula['IDAula'] ?>"
                            <?= ($curso['IDAula'] ?? '') == $aula['IDAula'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($aula['Numero']) ?> (<?= htmlspecialchars($aula['Edificio']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Horarios <span class="text-danger">*</span></label>
                <div class="d-flex gap-2 mb-1" style="font-size:.75rem;color:var(--bs-secondary);padding-left:4px">
                    <span style="width:180px">Día</span>
                    <span style="width:110px">Desde</span>
                    <span style="width:110px">Hasta</span>
                </div>
                <div id="horarios-container">
                <?php
                $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
                $filas = !empty($horarios) ? $horarios : [['Dia'=>'','HoraInicio'=>'','HoraFin'=>'']];
                foreach ($filas as $i => $h):
                ?>
                    <div class="horario-row d-flex gap-2 mb-2 align-items-center">
                        <select name="horarios[<?= $i ?>][Dia]" class="form-select form-select-sm horario-dia" style="width:180px" required>
                            <option value="">-- Día --</option>
                            <?php foreach ($dias as $dia): ?>
                                <option value="<?= $dia ?>" <?= ($h['Dia'] ?? '') === $dia ? 'selected' : '' ?>><?= $dia ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="time" name="horarios[<?= $i ?>][HoraInicio]" class="form-control form-control-sm horario-hi" style="width:110px" required
                               value="<?= htmlspecialchars(substr($h['HoraInicio'] ?? '',0,5)) ?>">
                        <input type="time" name="horarios[<?= $i ?>][HoraFin]" class="form-control form-control-sm horario-hf" style="width:110px" required
                               value="<?= htmlspecialchars(substr($h['HoraFin'] ?? '',0,5)) ?>">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-horario" <?= $i === 0 && count($filas) === 1 ? 'disabled' : '' ?>>
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
                </div>
                <button type="button" id="add-horario" class="btn btn-outline-secondary btn-sm mt-1">
                    <i class="bi bi-plus-lg me-1"></i>Agregar día
                </button>
                <p class="text-muted small mt-2 mb-0">
                    <i class="bi bi-clock me-1"></i>Carga horaria (32 semanas):
                    <strong id="carga-display">—</strong>
                </p>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i>Guardar
                </button>
                <a href="index.php?controller=admin&action=cursos" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const container  = document.getElementById('horarios-container');
    const addBtn     = document.getElementById('add-horario');
    const dias       = <?= json_encode(['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado']) ?>;
    let   rowCount   = container.querySelectorAll('.horario-row').length;

    function buildOptions(selected) {
        return '<option value="">-- Día --</option>' +
            dias.map(d => `<option value="${d}"${d === selected ? ' selected' : ''}>${d}</option>`).join('');
    }

    function recalcular() {
        let minutos = 0;
        container.querySelectorAll('.horario-row').forEach(row => {
            const hi = row.querySelector('.horario-hi').value;
            const hf = row.querySelector('.horario-hf').value;
            if (hi && hf && hf > hi) {
                const [hh, mm] = hi.split(':').map(Number);
                const [hf2, mf] = hf.split(':').map(Number);
                minutos += (hf2 * 60 + mf) - (hh * 60 + mm);
            }
        });
        const total = Math.round(minutos / 60 * 32);
        document.getElementById('carga-display').textContent = total > 0 ? total + ' hs' : '—';
    }

    function updateRemoveButtons() {
        const btns = container.querySelectorAll('.remove-horario');
        btns.forEach(b => b.disabled = btns.length === 1);
    }

    function renameFields() {
        container.querySelectorAll('.horario-row').forEach((row, i) => {
            row.querySelector('.horario-dia').name = `horarios[${i}][Dia]`;
            row.querySelector('.horario-hi').name  = `horarios[${i}][HoraInicio]`;
            row.querySelector('.horario-hf').name  = `horarios[${i}][HoraFin]`;
        });
    }

    addBtn.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'horario-row d-flex gap-2 mb-2 align-items-center';
        row.innerHTML = `
            <select name="horarios[${rowCount}][Dia]" class="form-select form-select-sm horario-dia" style="width:180px" required>
                ${buildOptions('')}
            </select>
            <input type="time" name="horarios[${rowCount}][HoraInicio]" class="form-control form-control-sm horario-hi" style="width:110px" required>
            <input type="time" name="horarios[${rowCount}][HoraFin]"    class="form-control form-control-sm horario-hf" style="width:110px" required>
            <button type="button" class="btn btn-outline-danger btn-sm remove-horario"><i class="bi bi-trash"></i></button>
        `;
        row.querySelector('.remove-horario').addEventListener('click', () => { row.remove(); renameFields(); updateRemoveButtons(); recalcular(); });
        row.querySelectorAll('input[type=time]').forEach(el => el.addEventListener('change', recalcular));
        container.appendChild(row);
        rowCount++;
        updateRemoveButtons();
    });

    container.querySelectorAll('.remove-horario').forEach(btn => {
        btn.addEventListener('click', () => { btn.closest('.horario-row').remove(); renameFields(); updateRemoveButtons(); recalcular(); });
    });

    container.querySelectorAll('input[type=time]').forEach(el => el.addEventListener('change', recalcular));

    updateRemoveButtons();
    recalcular();
})();
</script>
