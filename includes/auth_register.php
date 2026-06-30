<?php
require_once __DIR__ . "/auth_ui.php";

auth_ui_render_register_form([
    "id_suffix" => "standalone",
    "errors" => $registerErrors ?? [],
    "old" => $registerOld ?? [],
    "active" => true,
]);
?>
