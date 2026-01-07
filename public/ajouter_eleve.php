<?php
require_once '../config/db.php';
require_once '../config/session.php';
check_admin();
$pageTitle = 'Ajouter un Élève';

$classes = $pdo->query("SELECT * FROM classes ORDER BY nom_classe")->fetchAll();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $date_naissance = $_POST['date_naissance'];
    $genre = $_POST['genre'];
    $classe_id = $_POST['classe_id'];
    
    // Gestion photo
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
            $photo = $filename;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO eleves (nom, prenom, date_naissance, genre, classe_id, photo) VALUES (?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$nom, $prenom, $date_naissance, $genre, $classe_id, $photo]);
        header("Location: eleves.php");
        exit;
    } catch (PDOException $e) {
        $message = "Erreur: " . $e->getMessage();
    }
}

require_once '../templates/header.php';
?>

<div class="max-w-2xl mx-auto bg-white shadow sm:rounded-lg p-6">
    <?php if ($message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <div class="sm:col-span-3">
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" name="nom" required class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 border">
            </div>

            <div class="sm:col-span-3">
                <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                <input type="text" name="prenom" required class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 border">
            </div>

            <div class="sm:col-span-3">
                <label for="date_naissance" class="block text-sm font-medium text-gray-700">Date de Naissance</label>
                <input type="date" name="date_naissance" required class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 border">
            </div>

            <div class="sm:col-span-3">
                <label for="genre" class="block text-sm font-medium text-gray-700">Genre</label>
                <select name="genre" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    <option value="M">Garçon</option>
                    <option value="F">Fille</option>
                </select>
            </div>

            <div class="sm:col-span-6">
                <label for="classe_id" class="block text-sm font-medium text-gray-700">Classe</label>
                <select name="classe_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    <option value="">Sélectionner une classe</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom_classe']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="sm:col-span-6">
                <label for="photo" class="block text-sm font-medium text-gray-700">Photo</label>
                <input type="file" name="photo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
            </div>
        </div>

        <div class="flex justify-end">
            <a href="eleves.php" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 mr-3">Annuler</a>
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Enregistrer</button>
        </div>
    </form>
</div>

<?php require_once '../templates/footer.php'; ?>
