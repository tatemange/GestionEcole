<?php
require_once '../config/db.php';
$pageTitle = 'Liste des Élèves';

$search = $_GET['q'] ?? '';
$classe_id = $_GET['classe_id'] ?? '';

// Construction de la requête
$sql = "SELECT e.*, c.nom_classe 
        FROM eleves e 
        LEFT JOIN classes c ON e.classe_id = c.id 
        WHERE (e.nom LIKE :search OR e.prenom LIKE :search)";
$params = ['search' => "%$search%"];

if (!empty($classe_id)) {
    $sql .= " AND e.classe_id = :classe_id";
    $params['classe_id'] = $classe_id;
}

$sql .= " ORDER BY e.nom, e.prenom";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$eleves = $stmt->fetchAll();

// Récupération des classes pour le filtre
$classes = $pdo->query("SELECT * FROM classes ORDER BY nom_classe")->fetchAll();

require_once '../templates/header.php';
?>

<div class="max-w-7xl mx-auto">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Annuaire des Élèves</h1>
            <p class="mt-2 text-sm text-gray-700">Liste complète de tous les élèves inscrits.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <?php if (is_admin()): ?>
            <a href="ajouter_eleve.php" class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm">
                Nouveau Élève
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white p-4 shadow rounded-lg mb-6">
        <form class="sm:flex sm:items-end space-y-4 sm:space-y-0 sm:space-x-4" method="GET">
            <div class="flex-1">
                <label for="q" class="block text-sm font-medium text-gray-700">Rechercher</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <input type="text" name="q" id="q" value="<?= htmlspecialchars($search) ?>" class="focus:ring-green-500 focus:border-green-500 block w-full pl-3 sm:text-sm border-gray-300 rounded-md border p-2" placeholder="Nom ou prénom">
                </div>
            </div>
            <div class="w-full sm:w-48">
                <label for="classe_id" class="block text-sm font-medium text-gray-700">Classe</label>
                <select id="classe_id" name="classe_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md border">
                    <option value="">Toutes</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $classe_id ? 'selected' : '' ?>><?= htmlspecialchars($c['nom_classe']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm">
                Filtrer
            </button>
        </form>
    </div>

    <!-- Liste -->
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Élève
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Classe
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Genre
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($eleves as $eleve): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <?php if ($eleve['photo']): ?>
                                                <img class="h-10 w-10 rounded-full object-cover" src="uploads/<?= htmlspecialchars($eleve['photo']) ?>" alt="">
                                            <?php else: ?>
                                                <span class="inline-block h-10 w-10 rounded-full overflow-hidden bg-gray-100">
                                                    <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenom']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Né(e) le <?= date('d/m/Y', strtotime($eleve['date_naissance'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <?= htmlspecialchars($eleve['nom_classe'] ?? 'Non assigné') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $eleve['genre'] == 'M' ? 'Garçon' : 'Fille' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if (is_admin()): ?>
                                    <a href="modifier_eleve.php?id=<?= $eleve['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-4">Modifier</a>
                                    <a href="supprimer_eleve.php?id=<?= $eleve['id'] ?>" class="text-red-600 hover:text-red-900 mr-4" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                                    <?php endif; ?>
                                    <a href="bulletin.php?id=<?= $eleve['id'] ?>" class="text-green-600 hover:text-green-900 mr-4">Bulletin</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>
