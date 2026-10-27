<?php
/**
 * Reusable Data Table Component
 * Props:
 * - columns: array of ['key' => 'id', 'label' => '#', 'class' => 'text-center']
 * - rows: array of associative arrays
 * - emptyMessage: string
 */
$columns = $columns ?? [];
$rows = $rows ?? [];
$emptyMessage = $emptyMessage ?? 'No records found.';
?>
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
        <thead class="table-light text-muted">
            <tr>
                <?php foreach ($columns as $col): ?>
                    <th class="border-0 fw-semibold <?= e($col['class'] ?? '') ?>">
                        <?= e($col['label'] ?? '') ?>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="<?= count($columns) ?>" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                        <?= e($emptyMessage) ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <td class="<?= e($col['class'] ?? '') ?>">
                                <?php if (isset($col['render']) && is_callable($col['render'])): ?>
                                    <?= $col['render']($row) ?>
                                <?php else: ?>
                                    <?= e($row[$col['key']] ?? '') ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
