<div class="form-grid">
    <div class="form-row form-row--full">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" value="<?= admin_e($actor['name'] ?? '') ?>" required>
    </div>

    <div class="form-row form-row--full">
        <label for="avatar">Avatar URL</label>
        <input id="avatar" name="avatar" type="text" value="<?= admin_e($actor['avatar'] ?? '') ?>">
    </div>

    <div class="form-row form-row--full">
        <label for="profile_path">TMDB profile path</label>
        <input id="profile_path" name="profile_path" type="text" value="<?= admin_e($actor['profile_path'] ?? '') ?>">
    </div>

    <div class="form-row">
        <label for="known_for_department">Department</label>
        <input id="known_for_department" name="known_for_department" type="text" value="<?= admin_e($actor['known_for_department'] ?? '') ?>">
    </div>

    <div class="form-row form-row--full">
        <label for="biography">Biography</label>
        <textarea id="biography" name="biography"><?= admin_e($actor['biography'] ?? '') ?></textarea>
    </div>
</div>

<div class="form-actions">
    <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
    <a class="btn btn-secondary" href="<?= admin_e(admin_url('actors/index.php')) ?>">Cancel</a>
</div>
