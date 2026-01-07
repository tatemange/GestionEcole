<?php
require_once '../config/db.php';
// Header includes session check
$pageTitle = 'Gestion des Utilisateurs';
require_once '../templates/header.php';

check_admin(); // Security Check

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    if (!empty($nom) && !empty($email) && !empty($password)) {
        // Hash password
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare("INSERT INTO users (nom, email, role, password) VALUES (?, ?, ?, ?)");
        try {
            $stmt->execute([$nom, $email, $role, $hash]);
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Utilisateur créé !</div>';
        } catch (PDOException $e) {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Erreur : Email probable doublon.</div>';
        }
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Protection: Ne pas se supprimer soi-même
    if ($id == $_SESSION['user_id']) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Action impossible : Vous ne pouvez pas supprimer votre propre compte.</div>';
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        try {
            $stmt->execute([$id]);
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Utilisateur supprimé.</div>';
        } catch (PDOException $e) {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Erreur lors de la suppression.</div>';
        }
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="max-w-4xl mx-auto">
    <?= $message ?>
    
    <div class="bg-white shadow sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Ajouter un utilisateur</h3>
            <form class="mt-5 sm:flex sm:items-end space-x-4" method="POST">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                    <input type="text" name="nom" required class="mt-1 shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" required class="mt-1 shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
                <div class="w-32">
                    <label class="block text-sm font-medium text-gray-700">Rôle</label>
                    <select name="role" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none sm:text-sm">
                        <option value="professeur">Prof.</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
                    <input type="password" name="password" required class="mt-1 shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none sm:w-auto sm:text-sm">
                    Ajouter
                </button>
            </form>
        </div>
    </div>

    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($u['nom']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($u['email']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $u['role'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                        <?= ucfirst($u['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="?delete=<?= $u['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')">Supprimer</a>
                                    <?php else: ?>
                                    <span class="text-gray-400 italic">Moi</span>
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
