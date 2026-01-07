<?php
require_once '../config/db.php';
$pageTitle = 'Gestion des Notes';

$classe_id = $_GET['classe_id'] ?? null;
$matiere_id = $_GET['matiere_id'] ?? null;
$trimestre = $_GET['trimestre'] ?? 1;

$message = '';

// Enregistrement des notes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notes'])) {
    $count = 0;
    foreach ($_POST['notes'] as $eleve_id => $valeur) {
        if ($valeur === '') { 
            // Si vide, on supprime la note existante ?? Ou on ignore ? Ignorons pour l'instant ou supprimons si on veut "effacer".
            // Simplification : on traite seulement si numérique
            continue; 
        }
        
        // Vérification doublon / mise à jour
        // On regarde si une note existe déjà pour cet élève/matière/trimestre
        // NOTE: Le cahier des charges dit "ne pas saisir deux fois la même note pour un même examen". 
        // Ici on considère une note par matière par trimestre (moyenne de la matière). 
        // Si on voulait gérer plusieurs notes par matière, il faudrait une table 'examens'.
        // Vu la simplification "Calcul de la moyenne par matière : (Note * Coefficient)", cela suggère une note unique ou une moyenne déjà faite.
        // On va partir sur : UN champ de note par matière par trimestre (Bulletin trimestriel classique).
        
        $sqlCheck = "SELECT id FROM notes WHERE eleve_id = ? AND matiere_id = ? AND trimestre = ?";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$eleve_id, $matiere_id, $trimestre]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            $update = $pdo->prepare("UPDATE notes SET valeur_note = ? WHERE id = ?");
            $update->execute([$valeur, $existing['id']]);
        } else {
            $insert = $pdo->prepare("INSERT INTO notes (eleve_id, matiere_id, trimestre, valeur_note) VALUES (?, ?, ?, ?)");
            $insert->execute([$eleve_id, $matiere_id, $trimestre, $valeur]);
        }
        $count++;
    }
    $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">'.$count.' notes enregistrées !</div>';
}

// Récupération des listes
$classes = $pdo->query("SELECT * FROM classes ORDER BY nom_classe")->fetchAll();
if ($classe_id) {
    $stmtMat = $pdo->prepare("SELECT m.* FROM matieres m JOIN classe_matieres cm ON m.id = cm.matiere_id WHERE cm.classe_id = ? ORDER BY m.nom_matiere");
    $stmtMat->execute([$classe_id]);
    $matieres = $stmtMat->fetchAll();
} else {
    $matieres = $pdo->query("SELECT * FROM matieres ORDER BY nom_matiere")->fetchAll();
}

$eleves = [];
if ($classe_id && $matiere_id) {
    // Récupérer les élèves de la classe AVEC leurs notes s'il y en a
    $sql = "SELECT e.id, e.nom, e.prenom, n.valeur_note 
            FROM eleves e 
            LEFT JOIN notes n ON e.id = n.eleve_id AND n.matiere_id = :matiere_id AND n.trimestre = :trimestre
            WHERE e.classe_id = :classe_id 
            ORDER BY e.nom, e.prenom";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['matiere_id' => $matiere_id, 'trimestre' => $trimestre, 'classe_id' => $classe_id]);
    $eleves = $stmt->fetchAll();
}

require_once '../templates/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white p-6 shadow rounded-lg mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Sélectionner pour saisir</h2>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700">Classe</label>
                <select name="classe_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    <option value="">Choisir...</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $classe_id ? 'selected' : '' ?>><?= htmlspecialchars($c['nom_classe']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Matière</label>
                <select name="matiere_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    <option value="">Choisir...</option>
                    <?php foreach ($matieres as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= $m['id'] == $matiere_id ? 'selected' : '' ?>><?= htmlspecialchars($m['nom_matiere']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Trimestre</label>
                <select name="trimestre" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    <option value="1" <?= $trimestre == 1 ? 'selected' : '' ?>>Trimestre 1</option>
                    <option value="2" <?= $trimestre == 2 ? 'selected' : '' ?>>Trimestre 2</option>
                    <option value="3" <?= $trimestre == 3 ? 'selected' : '' ?>>Trimestre 3</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none">
                Charger la liste
            </button>
        </form>
    </div>

    <?= $message ?>

    <?php if ($classe_id && $matiere_id && !empty($eleves)): ?>
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Saisie des notes</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Note sur 20. Laisser vide pour ne pas noter.
            </p>
        </div>
        <form method="POST">
            <input type="hidden" name="classe_id" value="<?= $classe_id ?>">
            <input type="hidden" name="matiere_id" value="<?= $matiere_id ?>">
            <input type="hidden" name="trimestre" value="<?= $trimestre ?>">
            
            <ul class="divide-y divide-gray-200">
                <?php foreach ($eleves as $eleve): ?>
                <li class="px-4 py-4 sm:px-6 flex items-center justify-between hover:bg-gray-50">
                    <div class="text-sm font-medium text-gray-900">
                        <?= htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenom']) ?>
                    </div>
                    <div class="w-32">
                        <input type="number" step="0.01" min="0" max="20" name="notes[<?= $eleve['id'] ?>]" value="<?= $eleve['valeur_note'] ?>" class="shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border" placeholder="/ 20">
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Enregistrer les notes
                </button>
            </div>
        </form>
    </div>
    <?php elseif ($classe_id && $matiere_id): ?>
        <p class="text-gray-500 text-center mt-10">Aucun élève dans cette classe.</p>
    <?php endif; ?>
</div>

<?php require_once '../templates/footer.php'; ?>
