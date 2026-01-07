<?php
require_once '../config/db.php';
require_once '../config/session.php';
check_auth();
$pageTitle = 'Gestion des Classes';

// Traitement du formulaire d'ajout
$message = '';
// Traitement du formulaire d'ajout
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom_classe'])) {
    check_admin(); // Security
    if (!empty($_POST['nom_classe'])) {
        $stmt = $pdo->prepare("INSERT INTO classes (nom_classe) VALUES (?)");
        try {
            $stmt->execute([$_POST['nom_classe']]);
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Classe ajoutée avec succès !</div>';
        } catch (PDOException $e) {
             $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Erreur : ' . $e->getMessage() . '</div>';
        }
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Le nom de la classe est obligatoire.</div>';
    }
}

// Suppression
// Suppression
if (isset($_GET['delete'])) {
    check_admin(); // Security
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
    try {
        $stmt->execute([$id]);
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Classe supprimée.</div>';
    } catch (PDOException $e) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Impossible de supprimer : cette classe contient peut-être des élèves.</div>';
    }
}

// Récupération des classes
$stmt = $pdo->query("SELECT * FROM classes ORDER BY nom_classe");
$classes = $stmt->fetchAll();

require_once '../templates/header.php';
?>

<div class="max-w-4xl mx-auto">
    <?= $message ?>
    
    <div class="bg-white shadow sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Ajouter une nouvelle classe</h3>
            <div class="mt-2 max-w-xl text-sm text-gray-500">
                <p>Créer une classe (ex: 6ème A, Terminale S).</p>
            </div>
            <?php if (is_admin()): ?>
            <form class="mt-5 sm:flex sm:items-center" method="POST">
                <div class="w-full sm:max-w-xs">
                    <label for="nom_classe" class="sr-only">Nom de la classe</label>
                    <input type="text" name="nom_classe" id="nom_classe" class="shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border" placeholder="Ex: Terminale C">
                </div>
                <button type="submit" class="mt-3 w-full inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Ajouter
                </button>
            </form>
            <?php else: ?>
                <p class="mt-5 text-sm text-gray-500 italic">Vous devez être administrateur pour gérer les classes.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nom de la classe
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($classes as $classe): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($classe['nom_classe']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if (is_admin()): ?>
                                    <a href="?delete=<?= $classe['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</a>
                                    <?php endif; ?>
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
