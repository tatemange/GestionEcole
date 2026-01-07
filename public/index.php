<?php
require_once '../config/db.php';
require_once '../config/session.php';
check_auth();
$pageTitle = 'Tableau de Bord';

// Récupération des statistiques
$stmt = $pdo->query("SELECT COUNT(*) FROM eleves");
$nbEleves = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM classes");
$nbClasses = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM matieres");
$nbMatieres = $stmt->fetchColumn();

// Calcul de la moyenne de l'école (approximatif pour l'instant)
$stmt = $pdo->query("SELECT AVG(valeur_note) FROM notes");
$moyenneEcole = round($stmt->fetchColumn(), 2);

require_once '../templates/header.php';
?>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Card Élèves -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Élèves</dt>
                        <dd class="text-3xl font-semibold text-gray-900"><?= $nbEleves ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
                <a href="eleves.php" class="font-medium text-green-700 hover:text-green-900">Voir tous les élèves</a>
            </div>
        </div>
    </div>

    <!-- Card Classes -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Classes</dt>
                        <dd class="text-3xl font-semibold text-gray-900"><?= $nbClasses ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
                <a href="classes.php" class="font-medium text-blue-700 hover:text-blue-900">Gérer les classes</a>
            </div>
        </div>
    </div>

    <!-- Card Matières -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Matières</dt>
                        <dd class="text-3xl font-semibold text-gray-900"><?= $nbMatieres ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
                <a href="matieres.php" class="font-medium text-yellow-700 hover:text-yellow-900">Configurer les matières</a>
            </div>
        </div>
    </div>

    <!-- Card Moyenne -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Moyenne École</dt>
                        <dd class="text-3xl font-semibold text-gray-900"><?= $moyenneEcole ?>/20</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
                <a href="notes.php" class="font-medium text-purple-700 hover:text-purple-900">Voir les notes</a>
            </div>
        </div>
    </div>
</div>

<div class="mt-8">
    <h2 class="text-lg leading-6 font-medium text-gray-900">Accès Rapide</h2>
    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="ajouter_eleve.php" class="relative block rounded-lg border border-gray-300 bg-white shadow-sm px-6 py-4 cursor-pointer hover:border-gray-400 sm:flex sm:justify-between focus:outline-none">
            <div class="flex items-center">
                <div class="text-sm">
                    <p class="font-medium text-gray-900">Nouvel Inscription</p>
                    <p class="text-gray-500">Ajouter un élève à une classe</p>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-500 sm:mt-0 sm:ml-4 sm:text-right">
                <span class="text-green-600 hover:text-green-500 font-medium">Commencer &rarr;</span>
            </div>
        </a>
        <a href="saisie_notes.php" class="relative block rounded-lg border border-gray-300 bg-white shadow-sm px-6 py-4 cursor-pointer hover:border-gray-400 sm:flex sm:justify-between focus:outline-none">
            <div class="flex items-center">
                <div class="text-sm">
                    <p class="font-medium text-gray-900">Saisie des Notes</p>
                    <p class="text-gray-500">Ajouter des résultats d'examen</p>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-500 sm:mt-0 sm:ml-4 sm:text-right">
                <span class="text-green-600 hover:text-green-500 font-medium">Saisir &rarr;</span>
            </div>
        </a>
        
        <?php if (is_admin()): ?>
        <a href="users.php" class="relative block rounded-lg border border-red-200 bg-red-50 shadow-sm px-6 py-4 cursor-pointer hover:border-red-400 sm:flex sm:justify-between focus:outline-none">
            <div class="flex items-center">
                <div class="text-sm">
                    <p class="font-medium text-red-900">Gestion Utilisateurs</p>
                    <p class="text-red-700">Créer des comptes profs</p>
                </div>
            </div>
            <div class="mt-2 text-sm text-red-700 sm:mt-0 sm:ml-4 sm:text-right">
                <span class="font-medium">Gérer &rarr;</span>
            </div>
        </a>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>
