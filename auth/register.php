<?php
require_once '../includes/header.php';
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $password]);
        header('Location: /pms/auth/login.php');
        exit();
    } catch(PDOException $e) {
        $error = "Email already exists";
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100 py-12 px-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-900">Create Account</h2>
            <p class="mt-2 text-gray-600">Join our healthcare platform</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" required 
                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email address</label>
                <input type="email" name="email" required 
                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required 
                       class="mt-1 block w-full bg-white rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
            </div>

            <div>
                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Register
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 