<?php
?>
<nav class="navbar navbar-expand navbar-light navbar-bg">
    <?php if (in_array($_SESSION["dep_user_role"], ["AD", "DR"])) { ?>
        <a class="sidebar-toggle">
            <i class="hamburger align-self-center"></i>
        </a>
    <?php } ?>

    <div class="navbar-collapse collapse">
        <input type="text">
        <ul class="navbar-nav navbar-align">
            <li class="nav-item dropdown">
                <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="javascript:;" data-bs-toggle="dropdown">
                    <i class="align-middle" data-feather="settings"></i>
                </a>

                <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="javascript:;" data-bs-toggle="dropdown">
                    <img src="<?= (!empty($_SESSION["dep_user_avatar"])) ? $_SESSION["dep_user_avatar"] : "/" . BASE_URL . "assets/dist/img/user_avatar.png" ?>" onerror="src='/<?= BASE_URL ?>assets/dist/img/user_avatar.png'" class="avatar img-fluid rounded-circle me-1" alt="Avatar" /> <span class="text-dark"><?= explode(" ", $_SESSION["dep_user_name"])[0] . " " . explode(" ", $_SESSION["dep_user_lastname"])[0] ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="/<?= BASE_URL ?>edit-profile"><i class="align-middle me-1" data-feather="user"></i> Perfil</a>
                    <div class="dropdown-divider"></div>
                    <!--<a class="dropdown-item" href="javascript:;"><i class="align-middle me-1" data-feather="settings"></i> Configuración</a>-->
                    <a class="dropdown-item" href="/<?= BASE_URL ?>logout"><i class="align-middle me-1" data-feather="log-out"></i> Cerrar Sesión</a>
                </div>
            </li>
        </ul>
    </div>
</nav>