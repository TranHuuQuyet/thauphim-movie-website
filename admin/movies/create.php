<?php

require_once '../../includes/config.php';
require_once '../../includes/db.php';

$pdo = getDatabaseConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $stmt = $pdo->prepare("
        INSERT INTO movies
        (
            title,
            description,
            release_year,
            type,
            quality,
            status,
            is_premium
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['title'],
        $_POST['description'],
        $_POST['release_year'],
        $_POST['type'],
        $_POST['quality'],
        $_POST['status'],
        isset($_POST['is_premium']) ? 1 : 0
    ]);

    header("Location: index.php");
    exit;
}

include '../layout_header.php';
include '../layout_sidebar.php';
?>

<div class="admin-content">

<h1>Thêm phim</h1>

<form method="POST">

    <input
        type="text"
        name="title"
        placeholder="Tên phim"
        required
    >

    <br><br>

    <textarea
        name="description"
        placeholder="Mô tả"
    ></textarea>

    <br><br>

    <input
        type="number"
        name="release_year"
        placeholder="Năm"
    >

    <br><br>

    <select name="type">

        <option value="movie">
            Movie
        </option>

        <option value="series">
            Series
        </option>

    </select>

    <br><br>

    <input
        type="text"
        name="quality"
        value="HD"
    >

    <br><br>

    <select name="status">

        <option value="completed">
            Completed
        </option>

        <option value="ongoing">
            Ongoing
        </option>

        <option value="coming_soon">
            Coming Soon
        </option>

    </select>

    <br><br>

    <label>

        <input
            type="checkbox"
            name="is_premium"
        >

        Premium

    </label>

    <br><br>

    <button type="submit">

        Lưu phim

    </button>

</form>

</div>

<?php include '../layout_footer.php'; ?>