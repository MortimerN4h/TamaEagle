<?php
require_once 'config.php';
requireLogin(); // Redirect if not logged in
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Calculate base URL for JavaScript (simplified)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $tamaEaglePath = '';
    
    $currentPath = $_SERVER['REQUEST_URI'];
    $tamaEaglePos = strpos($currentPath, 'TamaEagle');
    if ($tamaEaglePos !== false) {
        $tamaEaglePath = substr($currentPath, 0, $tamaEaglePos) . 'TamaEagle/';
    }
    
    $baseUrl = $protocol . $host . $tamaEaglePath;
    ?>
    <meta name="base-url" content="<?php echo $baseUrl; ?>">
    <title>TamaEagle</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- jQuery UI for Drag & Drop -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <!-- Custom styles for notifications -->
    <style>
        #notification-badge {
            font-size: 0.65rem;
            transform: translate(-50%, -50%) !important;
        }
        #task-notifications .dropdown-item:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Top navigation -->
        <header class="main-header">
            <nav class="navbar navbar-expand navbar-light bg-light">
                <div class="container-fluid">
                    <button id="sidebarToggle" class="btn me-2">
                        <i class="bi bi-columns-gap"></i>
                    </button>
                    <a class="navbar-brand" href="../index.php">
                        <i class="fa fa-check-circle me-2"></i>TamaEagle
                    </a>
                    <div class="collapse navbar-collapse">
                        <ul class="navbar-nav ms-auto">
                            <!-- Notification Button -->
                            <li class="nav-item dropdown me-3">
                                <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-bell"></i>
                                    <span id="notification-badge" class="position-absolute top-20 start-100 translate-middle badge rounded-pill bg-danger">
                                        <span id="notification-count">0</span>
                                    </span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end p-0" style="width: 320px; max-height: 400px; overflow-y: auto;" aria-labelledby="notificationDropdown" id="task-notifications">
                                    <!-- Notifications will be loaded here dynamically -->
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fa fa-user-circle"></i> <?php echo $_SESSION['username']; ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#"><i class="fa fa-cog"></i> Settings</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="../auth/logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        <div class="content-container">
            <?php include_once 'sidebar.php'; ?>
            <div class="main-content overflow-x-auto">