<div class="form-grid">
    <div class="form-row form-row--full">
        <label for="movie_id">Movie</label>
        <select id="movie_id" name="movie_id" required>
            <option value="">Select movie</option>
            <?php foreach ($movies as $movie): ?>
                <option value="<?= (int) $movie['id'] ?>" <?= (string) ($episode['movie_id'] ?? '') === (string) $movie['id'] ? 'selected' : '' ?>>
                    <?= admin_e($movie['title'] . ' (' . $movie['type'] . ')') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-row">
        <label for="episode_number">Episode number</label>
        <input id="episode_number" name="episode_number" type="number" min="1" value="<?= admin_e($episode['episode_number'] ?? 1) ?>" required>
    </div>

    <div class="form-row">
        <label for="duration_seconds">Duration seconds</label>
        <input id="duration_seconds" name="duration_seconds" type="number" min="0" value="<?= admin_e($episode['duration_seconds'] ?? '') ?>">
    </div>

    <div class="form-row form-row--full">
        <label for="title">Title</label>
        <input id="title" name="title" type="text" value="<?= admin_e($episode['title'] ?? '') ?>" required>
    </div>

    <div class="form-row form-row--full">
        <label for="youtube_url">YouTube URL</label>
        <input id="youtube_url" name="youtube_url" type="url" value="<?= admin_e($episode['youtube_url'] ?? '') ?>" placeholder="https://www.youtube.com/embed/VIDEO_ID">
        <small class="muted">Accepted: youtube.com/watch, youtu.be, shorts/live, or embed links. Saved as a canonical embed URL.</small>
    </div>

    <div class="form-row form-row--full">
        <label>
            <input name="is_published" type="checkbox" value="1" <?= !empty($episode['is_published']) ? 'checked' : '' ?>>
            Publish this episode
        </label>
    </div>
</div>

<div class="form-actions">
    <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
    <a class="btn btn-secondary" href="<?= admin_e(admin_url('episodes/index.php')) ?>">Cancel</a>
</div>
