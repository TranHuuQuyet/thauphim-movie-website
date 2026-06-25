<?php

require_once '../../includes/config.php';
require_once '../../includes/db.php';

$pdo = getDatabaseConnection();

$movies = $pdo->query("
    SELECT *
    FROM movies
    ORDER BY id DESC
")->fetchAll();

include '../layout_header.php';
include '../layout_sidebar.php';
?>

<div class="admin-content">

    <div class="page-header">
        <h1>Movies</h1>

        <a href="create.php" class="btn-add">
            + Thêm phim
        </a>
    </div>

    <div class="admin-table">

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên phim</th>
                    <th>Năm</th>
                    <th>Loại</th>
                    <th>Premium</th>
                    <th>Thao tác</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach($movies as $movie): ?>

                <tr>

                    <td><?= $movie['id'] ?></td>

                    <td><?= htmlspecialchars($movie['title']) ?></td>

                    <td><?= $movie['release_year'] ?></td>

                    <td><?= $movie['type'] ?></td>

                    <td>
                        <?= $movie['is_premium'] ? 'Có' : 'Không' ?>
                    </td>

                    <td>

                        <a href="edit.php?id=<?= $movie['id'] ?>">
                            Sửa
                        </a>

                        |

                        <a
                            href="delete.php?id=<?= $movie['id'] ?>"
                            onclick="return confirm('Xóa phim?')"
                        >
                            Xóa
                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<?php include '../layout_footer.php'; ?>