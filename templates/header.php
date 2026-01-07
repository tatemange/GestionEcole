<?php require_once '../config/session.php'; check_auth(); ?>
<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco-Note - Gestion Scolaire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
        }
    </style>
</head>
<body class="h-full flex overflow-hidden">
    <!-- Sidebar -->
    <div class="hidden md:flex md:flex-shrink-0 no-print">
        <div class="flex flex-col w-64">
            <div class="flex flex-col h-0 flex-1 bg-green-800 shadow-xl">
                <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                    <div class="flex items-center flex-shrink-0 px-4 mb-5">
                        <span class="text-2xl font-bold text-white tracking-wider">ECO-NOTE</span>
                    </div>
                    <nav class="mt-5 flex-1 px-2 space-y-1">
                        <a href="index.php" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-green-700 transition">
                            <svg class="mr-3 flex-shrink-0 h-6 w-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Tableau de bord
                        </a>
                        <a href="eleves.php" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-green-100 hover:bg-green-700 hover:text-white transition">
                            <svg class="mr-3 flex-shrink-0 h-6 w-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Élèves
                        </a>
                        <a href="notes.php" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-green-100 hover:bg-green-700 hover:text-white transition">
                            <svg class="mr-3 flex-shrink-0 h-6 w-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Notes
                        </a>
                        <a href="classes.php" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-green-100 hover:bg-green-700 hover:text-white transition">
                            <svg class="mr-3 flex-shrink-0 h-6 w-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Classes
                        </a>
                        <a href="matieres.php" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-green-100 hover:bg-green-700 hover:text-white transition">
                            <svg class="mr-3 flex-shrink-0 h-6 w-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            Matières
                        </a>

                        <?php if (is_admin()): ?>
                        <div class="border-t border-green-700 my-2 pt-2">
                             <a href="users.php" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-green-100 hover:bg-green-700 hover:text-white transition">
                                <svg class="mr-3 flex-shrink-0 h-6 w-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" clip-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                                Utilisateurs
                            </a>
                        </div>
                        <?php endif; ?>
                    </nav>
                </div>
                <div class="flex-shrink-0 flex bg-green-900 p-4">
                    <div class="flex-shrink-0 w-full group block">
                        <div class="flex items-center">
                            <div class="ml-3">
                                <p class="text-sm font-medium text-white">
                                    <?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?>
                                </p>
                                <p class="text-xs font-medium text-green-300 group-hover:text-green-200">
                                    <?= htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'Invite')) ?>
                                </p>
                            </div>
                            <div class="ml-auto">
                                <a href="logout.php" class="text-white hover:text-red-400" title="Déconnexion">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow no-print">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    <?php echo isset($pageTitle) ? $pageTitle : 'Tableau de Bord'; ?>
                </h1>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto p-6">
