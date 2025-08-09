<?php
session_start();
if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin']) {
    header("Location: admin_panel.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Saththar Feeds - Admin Login">
    <title>Saththar Feeds - Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin_style.css">
    <link rel="shortcut icon" href="./assets/images/logo/image.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/react@17.0.2/umd/react.development.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/react-dom@17.0.2/umd/react-dom.development.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/babel-standalone@6.26.0/babel.min.js"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900 flex items-center justify-center min-h-screen">
    <div id="root"></div>
    <div id="error" class="text-red-600 text-center p-4" style="display:none;">React failed to render. Check console for errors.</div>

    <script type="text/babel">
        function AdminLogin() {
            const [username, setUsername] = React.useState('');
            const [password, setPassword] = React.useState('');
            const [error, setError] = React.useState('');
            const [isLoading, setIsLoading] = React.useState(false);

            const handleSubmit = (e) => {
                e.preventDefault();
                setIsLoading(true);
                setError('');

                fetch('admin_auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                })
                .then(response => response.json())
                .then(data => {
                    setIsLoading(false);
                    if (data.success) {
                        window.location.href = 'admin_panel.php';
                    } else {
                        setError('Invalid username or password');
                    }
                })
                .catch(() => {
                    setIsLoading(false);
                    setError('An error occurred. Please try again.');
                });
            };

            return (
                <div className="max-w-md w-full bg-white p-8 rounded-2xl shadow-xl card">
                    <div className="text-center mb-8">
                        <img src="./assets/images/logo/feeds.png" alt="Saththar Feeds Logo" className="h-16 mx-auto mb-4" />
                        <h2 className="text-3xl font-bold text-green-800">Admin Login</h2>
                    </div>
                    <form onSubmit={handleSubmit}>
                        <div className="mb-6">
                            <label htmlFor="username" className="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <input
                                type="text"
                                id="username"
                                value={username}
                                onChange={(e) => setUsername(e.target.value)}
                                className="w-full p-4 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="Enter username"
                                required
                                aria-label="Admin username"
                            />
                        </div>
                        <div className="mb-6">
                            <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input
                                type="password"
                                id="password"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                className="w-full p-4 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="Enter password"
                                required
                                aria-label="Admin password"
                            />
                        </div>
                        {error && <p className="text-red-500 text-sm mb-4">{error}</p>}
                        <button
                            type="submit"
                            className="w-full bg-green-500 text-white p-4 rounded-full hover:bg-green-600 transition-all duration-300 font-medium text-lg"
                            disabled={isLoading}
                        >
                            {isLoading ? 'Logging in...' : 'Login'}
                        </button>
                    </form>
                </div>
            );
        }

        try {
            ReactDOM.render(<AdminLogin />, document.getElementById('root'));
        } catch (e) {
            console.error('React rendering error:', e);
            document.getElementById('error').style.display = 'block';
        }
    </script>
</body>
</html>