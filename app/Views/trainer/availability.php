<div class="container-fluid p-0">
    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge" style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); border-radius: 9999px; font-weight: 700; padding: 4px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                    <i class="bi bi-clock-history me-1"></i> Working Hours
                </span>
                <span class="text-muted" style="font-size: 13px;">Trainer Schedule Setup</span>
            </div>
            <h2 class="h3 fw-bold mb-0 text-dark">Trainer Availability & Working Hours</h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="gf-btn gf-btn-primary" data-bs-toggle="modal" data-bs-target="#addAvailModal">
                <i class="bi bi-plus-circle me-1"></i> Add Availability Slot
            </button>
        </div>
    </div>

    <?php
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $recurring = array_filter($availabilities, fn($a) => $a['type'] === 'recurring');
        $exceptions = array_filter($availabilities, fn($a) => $a['type'] !== 'recurring');
    ?>

    <!-- Weekly Recurring Working Hours -->
    <div class="gf-card mb-4">
        <div class="p-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="background: rgba(0, 0, 0, 0.02);">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Weekly Recurring Availability</h5>
                <p class="small text-muted mb-0">Your standard repeating coaching hours across the week</p>
            </div>
            <span class="badge bg-light text-dark border">
                <?= count($recurring) ?> Active Slots
            </span>
        </div>
        <div class="p-3">
            <?php if (empty($recurring)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar3-range display-6 d-block mb-2 text-secondary"></i>
                    <div class="fw-semibold text-dark mb-1">No Recurring Hours Configured</div>
                    <span class="small">Click "Add Availability Slot" above to define your weekly coaching windows.</span>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php for ($d = 0; $d <= 6; $d++): ?>
                        <?php 
                            $slotsForDay = array_filter($recurring, fn($a) => (int)$a['day_of_week'] === $d);
                        ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <strong class="text-dark"><?= $days[$d] ?></strong>
                                    <?php if (!empty($slotsForDay)): ?>
                                        <span class="gf-badge active">Available</span>
                                    <?php else: ?>
                                        <span class="gf-badge neutral">Off</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (empty($slotsForDay)): ?>
                                    <div class="small text-muted fst-italic">No coaching hours configured</div>
                                <?php else: ?>
                                    <?php foreach ($slotsForDay as $slot): ?>
                                        <div class="d-flex align-items-center justify-content-between p-2 bg-white rounded-2 border mb-1">
                                            <span class="small fw-semibold text-dark">
                                                <i class="bi bi-clock text-primary me-1"></i>
                                                <?= date('h:i A', strtotime($slot['start_time'])) ?> - <?= date('h:i A', strtotime($slot['end_time'])) ?>
                                            </span>
                                            <form method="POST" action="<?= url('trainer/availability/' . $slot['id'] . '/delete') ?>" onsubmit="return confirm('Remove this availability slot?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Remove Slot">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Date-Specific Exceptions & Time Off -->
    <div class="gf-card">
        <div class="p-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="background: rgba(0, 0, 0, 0.02);">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Date-Specific Exceptions & Days Off</h5>
                <p class="small text-muted mb-0">Holidays, custom hours, or specific unavailable dates</p>
            </div>
            <span class="badge bg-light text-dark border">
                <?= count($exceptions) ?> Exceptions
            </span>
        </div>
        <div class="p-3">
            <?php if (empty($exceptions)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-check text-success display-6 d-block mb-2"></i>
                    <span>No date-specific exceptions or blackout days configured.</span>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th style="padding-left: 20px;">Date</th>
                                <th>Type</th>
                                <th>Time Window</th>
                                <th>Notes</th>
                                <th class="text-end" style="padding-right: 20px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exceptions as $ex): ?>
                                <tr>
                                    <td style="padding-left: 20px;">
                                        <strong class="text-dark"><?= date('M d, Y', strtotime($ex['specific_date'])) ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($ex['type'] === 'unavailable'): ?>
                                            <span class="gf-badge expired">Unavailable / Off</span>
                                        <?php else: ?>
                                            <span class="gf-badge expiring">Custom Hours</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small">
                                        <?= date('h:i A', strtotime($ex['start_time'])) ?> - <?= date('h:i A', strtotime($ex['end_time'])) ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?= e($ex['notes'] ?? '—') ?>
                                    </td>
                                    <td class="text-end" style="padding-right: 20px;">
                                        <form method="POST" action="<?= url('trainer/availability/' . $ex['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Remove this exception?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2">
                                                <i class="bi bi-trash3 me-1"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Availability Modal -->
<div class="modal fade" id="addAvailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: var(--gf-border); border-radius: var(--gf-radius-lg); box-shadow: var(--gf-shadow-hover);">
            <form method="POST" action="<?= url('trainer/availability') ?>">
                <?= csrf_field() ?>
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-clock-history text-primary me-1"></i> Add Availability Slot
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Schedule Type <span class="text-danger">*</span></label>
                        <select name="type" id="availTypeSelect" class="form-select" required style="border: var(--gf-border); border-radius: var(--gf-radius-md);" onchange="toggleAvailFields()">
                            <option value="recurring">Weekly Recurring Day</option>
                            <option value="exception">Specific Date (Custom Hours)</option>
                            <option value="unavailable">Specific Date (Unavailable / Leave)</option>
                        </select>
                    </div>

                    <!-- Day of week (for recurring) -->
                    <div class="mb-3" id="dayOfWeekGroup">
                        <label class="form-label small fw-semibold text-dark">Day of Week <span class="text-danger">*</span></label>
                        <select name="day_of_week" class="form-select" style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                            <option value="0">Sunday</option>
                        </select>
                    </div>

                    <!-- Specific Date (for exceptions) -->
                    <div class="mb-3 d-none" id="specificDateGroup">
                        <label class="form-label small fw-semibold text-dark">Specific Date <span class="text-danger">*</span></label>
                        <input type="date" name="specific_date" class="form-control" style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                    </div>

                    <!-- Time Window -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" value="09:00" required style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control" value="18:00" required style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Notes / Reason <span class="text-muted fw-normal">(Optional)</span></label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g., Morning Shift, Doctor Appointment..." style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="gf-btn gf-btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="gf-btn gf-btn-primary">
                        <i class="bi bi-check-circle-fill me-1"></i> Save Availability
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleAvailFields() {
    const type = document.getElementById('availTypeSelect').value;
    const dayGroup = document.getElementById('dayOfWeekGroup');
    const dateGroup = document.getElementById('specificDateGroup');

    if (type === 'recurring') {
        dayGroup.classList.remove('d-none');
        dateGroup.classList.add('d-none');
    } else {
        dayGroup.classList.add('d-none');
        dateGroup.classList.remove('d-none');
    }
}
</script>
