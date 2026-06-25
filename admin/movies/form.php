<div class="form-grid">
    <div class="form-row">
        <label for="title">Title</label>
        <input id="title" name="title" type="text" value="<?= admin_e($movie['title'] ?? '') ?>" required>
    </div>

    <div class="form-row">
        <label for="original_title">Original title</label>
        <input id="original_title" name="original_title" type="text" value="<?= admin_e($movie['original_title'] ?? '') ?>">
    </div>

    <div class="form-row form-row--full">
        <label for="description">Description</label>
        <textarea id="description" name="description"><?= admin_e($movie['description'] ?? '') ?></textarea>
    </div>

    <div class="form-row">
        <label for="release_date">Release date</label>
        <input id="release_date" name="release_date" type="date" value="<?= admin_e($movie['release_date'] ?? '') ?>">
    </div>

    <div class="form-row">
        <label for="release_year">Release year</label>
        <input id="release_year" name="release_year" type="number" min="1888" max="<?= (int) date('Y') + 5 ?>" value="<?= admin_e($movie['release_year'] ?? '') ?>">
    </div>

    <div class="form-row">
        <label for="runtime">Runtime seconds</label>
        <input id="runtime" name="runtime" type="number" min="0" value="<?= admin_e($movie['runtime'] ?? '') ?>">
    </div>

    <div class="form-row">
        <label for="type">Type</label>
        <select id="type" name="type">
            <option value="movie" <?= ($movie['type'] ?? '') === 'movie' ? 'selected' : '' ?>>Movie</option>
            <option value="series" <?= ($movie['type'] ?? '') === 'series' ? 'selected' : '' ?>>Series</option>
        </select>
    </div>

    <div class="form-row">
        <label for="quality">Quality</label>
        <input id="quality" name="quality" type="text" value="<?= admin_e($movie['quality'] ?? 'HD') ?>">
    </div>

    <div class="form-row">
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="completed" <?= ($movie['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="ongoing" <?= ($movie['status'] ?? '') === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
            <option value="coming_soon" <?= ($movie['status'] ?? '') === 'coming_soon' ? 'selected' : '' ?>>Coming soon</option>
        </select>
    </div>

    <div class="form-row">
        <label for="country_id">Country</label>
        <select id="country_id" name="country_id">
            <option value="">No country</option>
            <?php foreach ($countries as $country): ?>
                <option value="<?= (int) $country['id'] ?>" <?= (string) ($movie['country_id'] ?? '') === (string) $country['id'] ? 'selected' : '' ?>>
                    <?= admin_e($country['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-row form-row--full">
        <label for="poster">Poster URL</label>
        <input id="poster" name="poster" type="text" value="<?= admin_e($movie['poster'] ?? '') ?>">
    </div>

    <div class="form-row form-row--full">
        <label for="backdrop">Backdrop URL</label>
        <input id="backdrop" name="backdrop" type="text" value="<?= admin_e($movie['backdrop'] ?? '') ?>">
    </div>

    <div class="form-row form-row--full">
        <label>
            <input name="is_premium" type="checkbox" value="1" <?= !empty($movie['is_premium']) ? 'checked' : '' ?>>
            Premium movie
        </label>
    </div>
</div>

<div class="form-actions">
    <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
    <a class="btn btn-secondary" href="<?= admin_e(admin_url('movies/index.php')) ?>">Cancel</a>
</div>
