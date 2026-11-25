<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? APP_NAME); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo appUrl('assets/css/styles.css?v=' . filemtime(__DIR__ . '/../../public/assets/css/styles.css')); ?>">
</head>
<body>
    <div class="app-shell">
        <?php if ($page !== 'login'): ?>
            <aside class="sidebar">
                <div class="brand">
                    <div class="brand-mark">N</div>
                    <div>
                        <h1>Nursery</h1>
                        <p>School</p>
                    </div>
                    <button type="button" class="mobile-menu-toggle" aria-label="Open navigation menu" aria-controls="sidebarNav" aria-expanded="false">
                        <span aria-hidden="true">⋮</span>
                    </button>
                </div>

                <nav id="sidebarNav" class="sidebar-nav">
                    <?php if (($user['role'] ?? '') === 'parent'): ?>
                    <a href="<?php echo appUrl('index.php?page=family'); ?>" class="nav-item <?php echo $page === 'family' ? 'active' : ''; ?>">
                        <span>👨‍👩‍👧‍👦</span>
                        <span>My Children</span>
                    </a>
                    <?php else: ?>
                    <a href="<?php echo appUrl('index.php?page=dashboard'); ?>" class="nav-item <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                        <span>📊</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="<?php echo appUrl('index.php?page=pupils'); ?>" class="nav-item <?php echo $page === 'pupils' ? 'active' : ''; ?>">
                        <span>👦</span>
                        <span>Pupils</span>
                    </a>
                    <a href="<?php echo appUrl('index.php?page=parents'); ?>" class="nav-item <?php echo $page === 'parents' ? 'active' : ''; ?>">
                        <span>👨‍👩‍👧‍👦</span>
                        <span>Parents</span>
                    </a>
                    <a href="<?php echo appUrl('index.php?page=classes'); ?>" class="nav-item <?php echo $page === 'classes' ? 'active' : ''; ?>">
                        <span>🏫</span>
                        <span>Classes</span>
                    </a>
                    <a href="<?php echo appUrl('index.php?page=fees'); ?>" class="nav-item <?php echo $page === 'fees' ? 'active' : ''; ?>">
                        <span>💰</span>
                        <span>Fees</span>
                    </a>
                    <?php if (($user['role'] ?? '') === 'admin'): ?>
                        <a href="<?php echo appUrl('index.php?page=users'); ?>" class="nav-item <?php echo $page === 'users' ? 'active' : ''; ?>">
                            <span>👤</span>
                            <span>Users</span>
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo appUrl('index.php?page=reports'); ?>" class="nav-item <?php echo $page === 'reports' ? 'active' : ''; ?>">
                        <span>📈</span>
                        <span>Reports</span>
                    </a>
                    <a href="<?php echo appUrl('index.php?page=settings'); ?>" class="nav-item <?php echo $page === 'settings' ? 'active' : ''; ?>">
                        <span>⚙️</span>
                        <span>Settings</span>
                    </a>
                    <?php endif; ?>
                    <form method="post" action="<?php echo appUrl('index.php?page=dashboard'); ?>" class="nav-logout-form">
                        <input type="hidden" name="action" value="logout">
                        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                        <button type="submit" class="nav-item danger">
                            <span>🚪</span>
                            <span>Logout</span>
                        </button>
                    </form>
                </nav>
            </aside>
        <?php endif; ?>

        <main class="main-content <?php echo $page === 'login' ? 'login-main' : ''; ?>">
            <?php if ($page !== 'login'): ?>
                <header class="topbar">
                    <div>
                        <p class="eyebrow">Overview</p>
                        <h2><?php echo e($title ?? 'Dashboard'); ?></h2>
                    </div>

                    <div class="topbar-tools">
                        <div class="search-box">
                            <span>🔍</span>
                            <input type="search" placeholder="Search something..." aria-label="Search">
                        </div>
                        <button type="button" class="icon-button" aria-label="Notifications">🔔</button>
                        <div class="user-pill">
                            <div class="avatar"><?php echo e(strtoupper(substr($user['full_name'] ?? 'Admin', 0, 1))); ?></div>
                            <div>
                                <strong><?php echo e($user['full_name'] ?? 'Administrator'); ?></strong>
                                <small><?php echo e(ucfirst($user['role'] ?? 'admin')); ?></small>
                            </div>
                        </div>
                    </div>
                </header>
            <?php endif; ?>

            <?php if ($flash): ?>
                <div class="flash-message <?php echo e($flash['type']); ?>">
                    <?php echo e($flash['message']); ?>
                </div>
            <?php endif; ?>

            <?php echo $content; ?>
        </main>
    </div>

    <script src="<?php echo appUrl('assets/js/app.js?v=' . filemtime(__DIR__ . '/../../public/assets/js/app.js')); ?>"></script>
</body>
</html>
