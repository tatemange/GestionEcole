<?php
require_once '../config/db.php';

$eleve_id = $_GET['id'] ?? null;
$trimestre = $_GET['trimestre'] ?? 1;
$theme = $_GET['theme'] ?? 'indigo';

if (!$eleve_id) {
    header('Location: eleves.php');
    exit;
}

// Récupérer l'élève et sa classe
$stmt = $pdo->prepare("SELECT e.*, c.nom_classe FROM eleves e LEFT JOIN classes c ON e.classe_id = c.id WHERE e.id = ?");
$stmt->execute([$eleve_id]);
$eleve = $stmt->fetch();

if (!$eleve) die("Élève introuvable");

// Récupérer les notes de l'élève
if ($trimestre == 'annuel') {
    $stmt = $pdo->prepare("
        SELECT m.nom_matiere, m.coefficient, AVG(n.valeur_note) as valeur_note 
        FROM matieres m 
        JOIN classe_matieres cm ON m.id = cm.matiere_id
        LEFT JOIN notes n ON n.matiere_id = m.id AND n.eleve_id = ?
        WHERE cm.classe_id = ?
        GROUP BY m.id
        ORDER BY m.nom_matiere
    ");
    $stmt->execute([$eleve_id, $eleve['classe_id']]);
} else {
    $stmt = $pdo->prepare("
        SELECT m.nom_matiere, m.coefficient, n.valeur_note 
        FROM matieres m 
        JOIN classe_matieres cm ON m.id = cm.matiere_id
        LEFT JOIN notes n ON n.matiere_id = m.id AND n.eleve_id = ? AND n.trimestre = ?
        WHERE cm.classe_id = ?
        ORDER BY m.nom_matiere
    ");
    $stmt->execute([$eleve_id, $trimestre, $eleve['classe_id']]);
}
$notes = $stmt->fetchAll();

// Calculs
$totalPoints = 0;
$totalCoeffs = 0;

foreach ($notes as $n) {
    if ($n['valeur_note'] !== null) {
        $totalPoints += $n['valeur_note'] * $n['coefficient'];
        $totalCoeffs += $n['coefficient'];
    }
}

$moyenne = $totalCoeffs > 0 ? round($totalPoints / $totalCoeffs, 2) : 0;

// Mention
$mention = 'Insuffisant';
$decision = 'Redoublement envisagé'; // Par défaut
if ($moyenne >= 16) { $mention = 'Très Bien'; $decision = 'Félicitations'; }
elseif ($moyenne >= 14) { $mention = 'Bien'; $decision = 'Encouragements'; }
elseif ($moyenne >= 12) { $mention = 'Assez Bien'; $decision = 'Passage accordé'; }
elseif ($moyenne >= 10) { $mention = 'Passable'; $decision = 'Passage accordé'; }

// Classement
$stmtClass = $pdo->prepare("SELECT id FROM eleves WHERE classe_id = ?");
$stmtClass->execute([$eleve['classe_id']]);
$classmates = $stmtClass->fetchAll(PDO::FETCH_COLUMN);

$classe_averages_map = [];
foreach ($classmates as $cmId) {
    if ($trimestre == 'annuel') {
        $s_notes = $pdo->prepare("SELECT m.coefficient, AVG(n.valeur_note) as valeur_note FROM matieres m JOIN classe_matieres cm ON m.id = cm.matiere_id LEFT JOIN notes n ON n.matiere_id = m.id AND n.eleve_id = ? WHERE cm.classe_id = ? GROUP BY m.id");
        $s_notes->execute([$cmId, $eleve['classe_id']]); // Added group by and check for class matieres
    } else {
        $s_notes = $pdo->prepare("SELECT m.coefficient, n.valeur_note FROM matieres m JOIN notes n ON n.matiere_id = m.id WHERE n.eleve_id = ? AND n.trimestre = ?");
        $s_notes->execute([$cmId, $trimestre]);
    }
    $sn = $s_notes->fetchAll();
    
    $p = 0; $c = 0;
    foreach ($sn as $val) { 
        if ($val['valeur_note'] !== null) {
            $p += $val['valeur_note'] * $val['coefficient']; 
            $c += $val['coefficient']; 
        }
    }
    $classe_averages_map[$cmId] = $c > 0 ? $p / $c : 0;
}
arsort($classe_averages_map);
$rank = array_search($eleve_id, array_keys($classe_averages_map)) + 1;


// Theme colors mapping
$themes = [
    'indigo' => ['primary' => 'indigo', 'secondary' => 'blue', 'accent' => 'purple'],
    'green' => ['primary' => 'green', 'secondary' => 'emerald', 'accent' => 'teal'],
    'purple' => ['primary' => 'purple', 'secondary' => 'violet', 'accent' => 'fuchsia'],
    'red' => ['primary' => 'red', 'secondary' => 'rose', 'accent' => 'pink'],
    'orange' => ['primary' => 'orange', 'secondary' => 'amber', 'accent' => 'yellow'],
    'blue' => ['primary' => 'blue', 'secondary' => 'sky', 'accent' => 'cyan'],
];
$colors = $themes[$theme] ?? $themes['indigo'];

require_once '../templates/header.php'; // Ce header ne sera pas imprimé grâce au CSS print
?>

<div class="max-w-4xl mx-auto mb-10 no-print">
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    Pour imprimer ou sauvegarder en PDF, utilisez le bouton ci-dessous ou le raccourci <strong>Ctrl+P</strong>.
                </p>
            </div>
        </div>
    </div>
    
    <div class="flex justify-between items-center mb-4">
        <div class="flex space-x-2">
            <a href="?id=<?= $eleve_id ?>&trimestre=1&theme=<?= $theme ?>" class="px-3 py-1 rounded <?= $trimestre == 1 ? 'bg-'.$colors['primary'].'-600 text-white' : 'bg-white text-gray-700' ?>">Trimestre 1</a>
            <a href="?id=<?= $eleve_id ?>&trimestre=2&theme=<?= $theme ?>" class="px-3 py-1 rounded <?= $trimestre == 2 ? 'bg-'.$colors['primary'].'-600 text-white' : 'bg-white text-gray-700' ?>">Trimestre 2</a>
            <a href="?id=<?= $eleve_id ?>&trimestre=3&theme=<?= $theme ?>" class="px-3 py-1 rounded <?= $trimestre == 3 ? 'bg-'.$colors['primary'].'-600 text-white' : 'bg-white text-gray-700' ?>">Trimestre 3</a>
            <a href="?id=<?= $eleve_id ?>&trimestre=annuel&theme=<?= $theme ?>" class="px-3 py-1 rounded <?= $trimestre == 'annuel' ? 'bg-'.$colors['primary'].'-600 text-white' : 'bg-white text-gray-700' ?>">Annuel</a>
        </div>
        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-<?= $colors['primary'] ?>-600 hover:bg-<?= $colors['primary'] ?>-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-<?= $colors['primary'] ?>-500">
            <svg class="items-center mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Imprimer le Bulletin
        </button>
    </div>
    
    <!-- Color Theme Selector -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Palette de couleurs</h3>
        <div class="flex space-x-3">
            <a href="?id=<?= $eleve_id ?>&trimestre=<?= $trimestre ?>&theme=indigo" class="group relative">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600 shadow-md hover:shadow-lg transition-all <?= $theme == 'indigo' ? 'ring-4 ring-indigo-300 scale-110' : '' ?> cursor-pointer"></div>
                <span class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 text-xs text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Indigo</span>
            </a>
            <a href="?id=<?= $eleve_id ?>&trimestre=<?= $trimestre ?>&theme=green" class="group relative">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 shadow-md hover:shadow-lg transition-all <?= $theme == 'green' ? 'ring-4 ring-green-300 scale-110' : '' ?> cursor-pointer"></div>
                <span class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 text-xs text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Vert</span>
            </a>
            <a href="?id=<?= $eleve_id ?>&trimestre=<?= $trimestre ?>&theme=purple" class="group relative">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-violet-600 shadow-md hover:shadow-lg transition-all <?= $theme == 'purple' ? 'ring-4 ring-purple-300 scale-110' : '' ?> cursor-pointer"></div>
                <span class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 text-xs text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Violet</span>
            </a>
            <a href="?id=<?= $eleve_id ?>&trimestre=<?= $trimestre ?>&theme=red" class="group relative">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-red-500 to-rose-600 shadow-md hover:shadow-lg transition-all <?= $theme == 'red' ? 'ring-4 ring-red-300 scale-110' : '' ?> cursor-pointer"></div>
                <span class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 text-xs text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Rouge</span>
            </a>
            <a href="?id=<?= $eleve_id ?>&trimestre=<?= $trimestre ?>&theme=orange" class="group relative">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-500 to-amber-600 shadow-md hover:shadow-lg transition-all <?= $theme == 'orange' ? 'ring-4 ring-orange-300 scale-110' : '' ?> cursor-pointer"></div>
                <span class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 text-xs text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Orange</span>
            </a>
            <a href="?id=<?= $eleve_id ?>&trimestre=<?= $trimestre ?>&theme=blue" class="group relative">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-sky-600 shadow-md hover:shadow-lg transition-all <?= $theme == 'blue' ? 'ring-4 ring-blue-300 scale-110' : '' ?> cursor-pointer"></div>
                <span class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 text-xs text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Bleu</span>
            </a>
        </div>
    </div>
</div>

<!-- Bulletin Layout -->
<div class="bg-gradient-to-br from-<?= $colors['primary'] ?>-50 to-<?= $colors['secondary'] ?>-50 max-w-4xl mx-auto p-6 shadow-xl rounded-xl print:shadow-none print:w-full print:max-w-none print:rounded-none print:p-4 print:bg-white" id="bulletin">
    <!-- Header -->
    <div class="flex justify-between items-start bg-white rounded-lg p-4 mb-4 shadow-sm print:shadow-none print:rounded-none">
        <div class="w-2/3">
            <h1 class="text-xl font-bold text-<?= $colors['primary'] ?>-700 uppercase tracking-wide">Bulletin Scolaire</h1>
            <p class="text-gray-600 text-xs mt-1">Année scolaire 2024-2025</p>
            <p class="text-xs text-gray-500">Établissement Eco-School</p>
            <div class="mt-2 text-sm">
                <span class="font-semibold text-gray-700">Classe :</span> <span class="text-gray-600"><?= htmlspecialchars($eleve['nom_classe']) ?></span>
                <span class="mx-2">•</span>
                <span class="font-semibold text-gray-700">Période :</span> <span class="text-gray-600"><?= $trimestre == 'annuel' ? 'Annuelle' : 'Trimestre ' . $trimestre ?></span>
            </div>
        </div>
        <div class="w-1/3 flex justify-end">
            <?php if ($eleve['photo']): ?>
                <img src="uploads/<?= htmlspecialchars($eleve['photo']) ?>" alt="Photo" class="h-20 w-20 object-cover rounded-lg border-2 border-<?= $colors['primary'] ?>-200 shadow-sm">
            <?php else: ?>
                <div class="h-20 w-20 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-400 text-xs border-2 border-gray-200 rounded-lg shadow-sm">Photo</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info Eleve -->
    <div class="mb-4 p-3 bg-white rounded-lg shadow-sm">
        <h2 class="text-base font-bold text-gray-800"><?= htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenom']) ?></h2>
        <div class="text-xs text-gray-600 mt-1">
            <span class="font-medium">Né(e) le :</span> <?= date('d/m/Y', strtotime($eleve['date_naissance'])) ?>
        </div>
    </div>

    <!-- Notes Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-4">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gradient-to-r from-<?= $colors['primary'] ?>-600 to-<?= $colors['secondary'] ?>-600 text-white">
                    <th class="px-3 py-2 text-left text-xs font-semibold">Matière</th>
                    <th class="px-2 py-2 text-center text-xs font-semibold">Coeff.</th>
                    <th class="px-2 py-2 text-center text-xs font-semibold">Note/20</th>
                    <th class="px-2 py-2 text-center text-xs font-semibold">Points</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold">Appréciation</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($notes as $note): ?>
                <tr class="hover:bg-<?= $colors['primary'] ?>-50 transition-colors">
                    <td class="px-3 py-1.5 text-xs font-medium text-gray-900"><?= htmlspecialchars($note['nom_matiere']) ?></td>
                    <td class="px-2 py-1.5 text-center text-xs text-gray-700"><?= $note['coefficient'] ?></td>
                    <td class="px-2 py-1.5 text-center text-xs font-bold <?= $note['valeur_note'] !== null ? ($note['valeur_note'] >= 10 ? 'text-green-600' : 'text-red-600') : 'text-gray-400' ?>">
                        <?= $note['valeur_note'] !== null ? number_format($note['valeur_note'], 2) : '-' ?>
                    </td>
                    <td class="px-2 py-1.5 text-center text-xs text-gray-600">
                        <?= $note['valeur_note'] !== null ? number_format($note['valeur_note'] * $note['coefficient'], 2) : '-' ?>
                    </td>
                    <td class="px-3 py-1.5 text-xs italic text-gray-600">
                        <?php 
                            if ($note['valeur_note'] === null) echo '';
                            elseif ($note['valeur_note'] >= 16) echo '✓ Excellent';
                            elseif ($note['valeur_note'] >= 14) echo '✓ Très bien';
                            elseif ($note['valeur_note'] >= 12) echo '✓ Bon';
                            elseif ($note['valeur_note'] >= 10) echo '≈ Convenable';
                            else echo '⚠ À améliorer';
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-gradient-to-r from-gray-100 to-gray-200">
                <tr class="font-bold">
                    <td class="px-3 py-2 text-xs text-right text-gray-800">TOTAUX</td>
                    <td class="px-2 py-2 text-center text-xs text-gray-800"><?= $totalCoeffs ?></td>
                    <td class="px-2 py-2 text-center text-xs text-gray-500">-</td>
                    <td class="px-2 py-2 text-center text-xs text-gray-800"><?= number_format($totalPoints, 2) ?></td>
                    <td class="px-3 py-2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 <?= $moyenne >= 10 ? 'border-green-500' : 'border-red-500' ?>">
            <h3 class="font-bold text-sm text-gray-800 mb-3">Bilan <?= $trimestre == 'annuel' ? 'Annuel' : 'Trimestriel' ?></h3>
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-600">Moyenne Générale :</span>
                    <span class="text-xl font-bold <?= $moyenne < 10 ? 'text-red-600' : 'text-green-600' ?>"><?= number_format($moyenne, 2) ?>/20</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-600">Rang :</span>
                    <span class="text-sm font-semibold text-<?= $colors['primary'] ?>-700"><?= $rank ?>er/ème</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-600">Mention :</span>
                    <span class="text-sm font-semibold text-<?= $colors['accent'] ?>-700"><?= $mention ?></span>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-<?= $colors['primary'] ?>-50 to-<?= $colors['secondary'] ?>-50 rounded-lg shadow-sm p-4 border border-<?= $colors['primary'] ?>-200">
            <h3 class="font-bold text-sm text-gray-800 mb-2">Décision du Conseil</h3>
            <p class="text-base italic text-center mt-6 font-medium text-<?= $colors['primary'] ?>-700"><?= $decision ?></p>
        </div>
    </div>

    <!-- Signatures -->
    <div class="flex justify-between mt-8 pt-6 border-t border-gray-300 print:mt-6">
        <div class="text-center w-1/3">
            <p class="mb-8 text-xs font-semibold text-gray-700">Le Professeur Principal</p>
            <div class="h-px bg-gray-400 w-3/4 mx-auto"></div>
        </div>
        <div class="text-center w-1/3">
            <p class="mb-8 text-xs font-semibold text-gray-700">Le Directeur</p>
            <div class="h-px bg-gray-400 w-3/4 mx-auto"></div>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>
