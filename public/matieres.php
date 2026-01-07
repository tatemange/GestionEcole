<?php
require_once '../config/db.php';
require_once '../config/session.php';
check_auth();
$pageTitle = 'Gestion des Matières';

// Ajout
// Ajout
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom_matiere'])) {
    check_admin(); // Security
    if (!empty($_POST['nom_matiere'])) {
        $stmt = $pdo->prepare("INSERT INTO matieres (nom_matiere, coefficient) VALUES (?, ?)");
        try {
            $coeff = !empty($_POST['coefficient']) ? $_POST['coefficient'] : 1;
            $stmt->execute([$_POST['nom_matiere'], $coeff]);
            $matiere_id = $pdo->lastInsertId();
            
            if (!empty($_POST['classes']) && is_array($_POST['classes'])) {
                $stmtLink = $pdo->prepare("INSERT INTO classe_matieres (classe_id, matiere_id) VALUES (?, ?)");
                foreach ($_POST['classes'] as $c_id) {
                    $stmtLink->execute([$c_id, $matiere_id]);
                }
            }

            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Matière ajoutée !</div>';
        } catch (PDOException $e) {
             $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Erreur : ' . $e->getMessage() . '</div>';
        }
    }
}

// Suppression
// Suppression
if (isset($_GET['delete'])) {
    check_admin(); // Security
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM matieres WHERE id = ?");
    try {
        $stmt->execute([$id]);
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Matière supprimée.</div>';
    } catch (PDOException $e) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Erreur suppression.</div>';
    }
}

$stmt = $pdo->query("SELECT * FROM matieres ORDER BY nom_matiere");
$matieres = $stmt->fetchAll();

$stmtC = $pdo->query("SELECT * FROM classes ORDER BY nom_classe");
$classes = $stmtC->fetchAll();

require_once '../templates/header.php';
?>

<div class="max-w-4xl mx-auto">
    <?= $message ?>
    
    <div class="bg-white shadow sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Ajouter une matière</h3>
            <?php if (is_admin()): ?>
            <form class="mt-5 sm:flex sm:items-end space-x-4" method="POST">
                <div class="flex-1">
                    <label for="nom_matiere" class="block text-sm font-medium text-gray-700">Nom</label>
                    <input type="text" name="nom_matiere" id="nom_matiere" class="mt-1 shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border" placeholder="Ex: Mathématiques">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Classes Concernées</label>
                    <div class="grid grid-cols-2 gap-2 h-32 overflow-y-auto border border-gray-300 rounded-md p-2 bg-gray-50">
                        <?php foreach ($classes as $c): ?>
                            <div class="flex items-center">
                                <input id="class_<?= $c['id'] ?>" name="classes[]" type="checkbox" value="<?= $c['id'] ?>" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                <label for="class_<?= $c['id'] ?>" class="ml-2 block text-sm text-gray-900 truncate" title="<?= htmlspecialchars($c['nom_classe']) ?>">
                                    <?= htmlspecialchars($c['nom_classe']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="w-32">
                    <label for="coefficient" class="block text-sm font-medium text-gray-700">Coefficient</label>
                    <input type="number" step="0.1" name="coefficient" id="coefficient" value="1" class="mt-1 shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:w-auto sm:text-sm">
                    Ajouter
                </button>
            </form>
            <?php else: ?>
                <p class="mt-5 text-sm text-gray-500 italic">Vous devez être administrateur pour ajouter des matières.</p>
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matière</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coefficient</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($matieres as $m): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($m['nom_matiere']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($m['coefficient']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if (is_admin()): ?>
                                    <a href="?delete=<?= $m['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Confirmer ?')">Supprimer</a>
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
