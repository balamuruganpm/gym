<?php if (has_flash('success')): ?>
    <div class="gf-alert gf-alert-success" data-auto-dismiss>
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div class="flex-grow-1"><?= e(flash('success')) ?></div>
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    </div>
<?php endif; ?>

<?php if (has_flash('error')): ?>
    <div class="gf-alert gf-alert-error" data-auto-dismiss>
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div class="flex-grow-1"><?= e(flash('error')) ?></div>
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    </div>
<?php endif; ?>

<?php if (has_flash('errors')): ?>
    <div class="gf-alert gf-alert-error">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div class="flex-grow-1">
            <ul class="mb-0 ps-3">
                <?php foreach (flash('errors') as $fieldErrors): ?>
                    <?php foreach ($fieldErrors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    </div>
<?php endif; ?>
