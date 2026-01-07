<?php
require_once '../config/db.php';
require_once '../config/session.php';
check_admin();
$pageTitle = 'Modifier un Élève';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: eleves.php');
    exit;
}

// Récupération élève
$stmt = $pdo->prepare("SELECT * FROM eleves WHERE id = ?");
$stmt->execute([$id]);
$eleve = $stmt->fetch();

if (!$eleve) {
    die("Élève introuvable");
}

$classes = $pdo->query("SELECT * FROM classes ORDER BY nom_classe")->fetchAll();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $date_naissance = $_POST['date_naissance'];
    $genre = $_POST['genre'];
    $classe_id = $_POST['classe_id'];
    
    // Logic photo
    $photo = $eleve['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $uploadDir = 'uploads/';
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
            $photo = $filename;
        }
    }

    $sql = "UPDATE eleves SET nom = ?, prenom = ?, date_naissance = ?, genre = ?, classe_id = ?, photo = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([$nom, $prenom, $date_naissance, $genre, $classe_id, $photo, $id]);
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Modifications enregistrées !</div>';
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM eleves WHERE id = ?");
        $stmt->execute([$id]);
        $eleve = $stmt->fetch();
    } catch (PDOException $e) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Erreur : ' . $e->getMessage() . '</div>';
    }
}

require_once '../templates/header.php';
?>

<div class="max-w-2xl mx-auto bg-white shadow sm:rounded-lg p-6">
    <?= $message ?>
    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <div class="sm:col-span-3">
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($eleve['nom']) ?>" required class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 border">
            </div>

            <div class="sm:col-span-3">
                <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                <input type="text" name="prenom" value="<?= htmlspecialchars($eleve['prenom']) ?>" required class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 border">
            </div>

            <div class="sm:col-span-3">
                <label for="date_naissance" class="block text-sm font-medium text-gray-700">Date de Naissance</label>
                <input type="date" name="date_naissance" value="<?= $eleve['date_naissance'] ?>" required class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 border">
            </div>

            <div class="sm:col-span-3">
                <label for="genre" class="block text-sm font-medium text-gray-700">Genre</label>
                <select name="genre" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    <option value="M" <?= $eleve['genre'] == 'M' ? 'selected' : '' ?>>Garçon</option>
                    <option value="F" <?= $eleve['genre'] == 'F' ? 'selected' : '' ?>>Fille</option>
                </select>
            </div>

            <div class="sm:col-span-6">
                <label for="classe_id" class="block text-sm font-medium text-gray-700">Classe</label>
                <select name="classe_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    <option value="">Sélectionner une classe</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $eleve['classe_id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nom_classe']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="sm:col-span-6">
                <label class="block text-sm font-medium text-gray-700">Photo actuelle</label>
                <div class="mt-2 flex items-center">
                    <?php if ($eleve['photo']): ?>
                        <img class="inline-block h-12 w-12 rounded-full overflow-hidden bg-gray-100" src="uploads/<?= htmlspecialchars($eleve['photo']) ?>" alt="">
                    <?php else: ?>
                        <span class="inline-block h-12 w-12 rounded-full overflow-hidden bg-gray-100">
                             <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sm:col-span-6">
                <label for="photo" class="block text-sm font-medium text-gray-700">Changer la photo</label>
                <input type="file" name="photo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500">
            </div>
        </div>

        <div class="flex justify-between">
            <a href="supprimer_eleve.php?id=<?= $eleve['id'] ?>" class="text-red-600 hover:text-red-900 font-medium" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élève ?')">Supprimer l'élève</a>
            <div class="flex">
                <a href="eleves.php" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 mr-3">Retour</a>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Enregistrer</button>
            </div>
            
        </div>
    </form>
</div>

<?php require_once '../templates/footer.php'; ?>
