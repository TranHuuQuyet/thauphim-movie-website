<?php
require_once __DIR__ . "/auth_ui.php";

$authLoginErrors = $_SESSION["login_errors"] ?? [];
$authLoginNotice = trim((string) ($_SESSION["login_notice"] ?? ""));
$authLoginOld = $_SESSION["login_old"] ?? [];
$authModalActive = !empty($authLoginErrors) || $authLoginNotice !== "";
?>
<div class="auth-modal <?= $authModalActive ? "active" : "" ?>" id="authModal" data-auth-modal>
    <?php
    auth_ui_render_surface([
        "context" => "modal",
        "mode" => "login",
        "id_suffix" => "modal",
        "login_errors" => $authLoginErrors,
        "login_notices" => $authLoginNotice !== "" ? [$authLoginNotice] : [],
        "login_old" => $authLoginOld,
    ]);
    ?>
</div>
<?php
unset($_SESSION["login_errors"], $_SESSION["login_notice"], $_SESSION["login_old"]);
?>
