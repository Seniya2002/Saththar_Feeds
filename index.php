<?php
session_start();
$username = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? ($_SESSION['username'] ?? 'Guest') : null;
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

date_default_timezone_set('Asia/Kolkata');
$currentDateTime = date('F d, Y, h:i A');

require_once 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Saththar Feeds - Smart Pet Shop Management System">
    <title>Saththar Feeds - Smart Pet Shop</title>
    <link rel="shortcut icon" href="./assets/images/logo/image.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .animate-panel {
            animation: slideIn 0.5s ease-out forwards;
        }
        .animate-text-up {
            animation: textUp 0.8s ease-out forwards;
        }
        .animate-text-up-delay {
            animation: textUp 0.8s ease-out 0.2s forwards;
        }
        .animate-pulse-btn {
            animation: pulse 2s infinite;
        }
        .glass-btn {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes textUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .notification-popup {
            animation: slideIn 0.5s ease-out forwards;
        }
        .product-image {
            width: 100%;
            height: 300px;
            object-fit: contain;
            object-position: center;
            background: #f9fafb;
            border-radius: 1rem;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/react@17.0.2/umd/react.development.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/react-dom@17.0.2/umd/react-dom.development.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/babel-standalone@6.26.0/babel.min.js"></script>
    <script src="https://unpkg.com/@heroicons/react@2.1.5/outline.js"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <div id="root"></div>
    <div id="error" className="text-red-600 text-center p-4" style={{display: 'none'}}></div>

    <script type="text/babel">
        function Navbar({ username, cartCount, notificationCount, setNotificationCount }) {
            const [isMenuOpen, setIsMenuOpen] = React.useState(false);
            const [showPopup, setShowPopup] = React.useState(false);
            const [notificationDetails, setNotificationDetails] = React.useState(null);

            const toggleMenu = () => setIsMenuOpen(!isMenuOpen);

            React.useEffect(() => {
                const handleScroll = () => {
                    const navbar = document.getElementById('navbar');
                    if (window.scrollY > 50) {
                        navbar.classList.remove('bg-transparent', 'text-white');
                        navbar.classList.add('bg-white', 'text-green-800', 'shadow-lg');
                    } else {
                        navbar.classList.remove('bg-white', 'text-green-800', 'shadow-lg');
                        navbar.classList.add('bg-transparent', 'text-white');
                    }
                };
                window.addEventListener('scroll', handleScroll);
                return () => window.removeEventListener('scroll', handleScroll);
            }, []);

            React.useEffect(() => {
                const fetchNotifications = () => {
                    fetch('get_notifications.php', { credentials: 'include' })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.json();
                        })
                        .then(data => {
                            if (data.success && data.notifications) {
                                setNotificationCount(data.count || 0);
                                const approvalNotification = data.notifications.find(n => n.order_status === 'approved' && !n.viewed);
                                if (approvalNotification) {
                                    const notificationDate = new Date().toLocaleString('en-US', {
                                        month: 'long',
                                        day: '2-digit',
                                        year: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        hour12: true
                                    });
                                    setNotificationDetails({
                                        orderId: approvalNotification.order_id,
                                        message: `Your order #${approvalNotification.order_id} has been ${approvalNotification.order_status} by the vendor at ${notificationDate}!`,
                                        status: approvalNotification.order_status
                                    });
                                    setShowPopup(true);
                                }
                            } else {
                                console.error('Error fetching notifications:', data.message || 'No notifications data');
                            }
                        })
                        .catch(error => console.error('Error fetching notifications:', error));
                };

                fetchNotifications();
                const interval = setInterval(fetchNotifications, 30000); // Poll every 30 seconds
                return () => clearInterval(interval);
            }, [setNotificationCount]);

            const handleNotificationClick = () => {
                if (notificationCount > 0) {
                    fetch('get_notifications.php', { credentials: 'include' })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.json();
                        })
                        .then(data => {
                            if (data.success && data.notifications && data.notifications.length > 0) {
                                const latestNotification = data.notifications.find(n => !n.viewed) || data.notifications[0];
                                const notificationDate = new Date(latestNotification.created_at || new Date()).toLocaleString('en-US', {
                                    month: 'long',
                                    day: '2-digit',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: true
                                });
                                setNotificationDetails({
                                    orderId: latestNotification.order_id,
                                    message: latestNotification.order_status === 'approved' 
                                        ? `Your order #${latestNotification.order_id} has been approved by the vendor at ${notificationDate}!`
                                        : latestNotification.message,
                                    status: latestNotification.order_status
                                });
                                setShowPopup(true);
                            }
                        })
                        .catch(error => console.error('Error fetching notification details:', error));
                }
            };

            return (
                <nav id="navbar" className="fixed w-full z-50 transition-all duration-300 bg-transparent text-white">
                    <div className="container mx-auto px-6 py-4 flex justify-between items-center">
                        <div className="flex items-center">
                            <img src="./assets/images/logo/feeds.png" alt="Saththar Feeds Logo" className="h-12 transition-transform duration-300 hover:scale-105" />
                            <h1 className="ml-4 text-2xl font-bold">{username ? `Welcome, ${username}` : 'Saththar Feeds'}</h1>
                        </div>
                        <div className="space-x-6 md:flex hidden items-center">
                            <a href="#home" className="hover:text-green-400 transition-colors duration-200" title="Home"><svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7m-9 5v6h4v-6m2-2h.01"></path></svg></a>
                            <a href="products.php" className="hover:text-green-400 transition-colors duration-200" title="Shop"><svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 3h18l-1.68 9H5.68L3 3zm2 12h14m-7 4h.01"></path></svg></a>
                            {username && <a href="profile.php" className="hover:text-green-400 transition-colors duration-200" title="Profile"><svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zm-4 7a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></a>}
                            <a href="#shop" className="hover:text-green-400 transition-colors duration-200" title="Recommendations"><svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></a>
                            <a href="cart.php" className="hover:text-green-400 transition-colors duration-200 relative" title="Cart"><svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.6 6.4M17 13l2.6 6.4M9 20a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"></path></svg>{cartCount > 0 && <span className="absolute -top-3 -right-3 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center">{cartCount}</span>}</a>
                            <a href="#" onClick={handleNotificationClick} className="hover:text-green-400 transition-colors duration-200 relative" title="Notifications"><svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>{notificationCount > 0 && <span className="absolute -top-3 -right-3 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{notificationCount}</span>}</a>
                            <a href={username ? 'logout.php' : 'login.php'} className="hover:text-green-400 transition-colors duration-200" title={username ? 'Logout' : 'Login'}><svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={username ? "M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-6 0V7a3 3 0 016 0v1" : "M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"}></path></svg></a>
                        </div>
                        <div className="md:hidden">
                            <button onClick={toggleMenu} className="focus:outline-none text-white" aria-expanded={isMenuOpen} aria-label="Toggle navigation menu"><svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16m-7 6h7" /></svg></button>
                        </div>
                    </div>
                    <div className={`md:hidden ${isMenuOpen ? 'block' : 'hidden'} bg-green-700 text-white p-6 space-y-4 shadow-lg`}>
                        <a href="#home" className="block hover:text-gray-200 transition-colors duration-200 flex justify-center" title="Home"><svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7m-9 5v6h4v-6m2-2h.01"></path></svg></a>
                        <a href="products.php" className="block hover:text-gray-200 transition-colors duration-200 flex justify-center" title="Shop"><svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 3h18l-1.68 9H5.68L3 3zm2 12h14m-7 4h.01"></path></svg></a>
                        {username && <a href="profile.php" className="block hover:text-gray-200 transition-colors duration-200 flex justify-center" title="Profile"><svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zm-4 7a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></a>}
                        <a href="#shop" className="block hover:text-gray-200 transition-colors duration-200 flex justify-center" title="Recommendations"><svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></a>
                        <a href="cart.php" className="block hover:text-gray-200 transition-colors duration-200 flex justify-center relative" title="Cart"><svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.6 6.4M17 13l2.6 6.4M9 20a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"></path></svg>{cartCount > 0 && <span className="absolute -top-3 right-0 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{cartCount}</span>}</a>
                        <a href="#" onClick={handleNotificationClick} className="block hover:text-gray-200 transition-colors duration-200 flex justify-center relative" title="Notifications"><svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>{notificationCount > 0 && <span className="absolute -top-3 right-0 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{notificationCount}</span>}</a>
                        <a href={username ? 'logout.php' : 'login.php'} className="block hover:text-gray-200 transition-colors duration-200 flex justify-center" title={username ? 'Logout' : 'Login'}><svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={username ? "M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-6 0V7a3 3 0 016 0v1" : "M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"}></path></svg></a>
                    </div>
                    {showPopup && notificationDetails && <NotificationsPopup details={notificationDetails} onClose={() => { 
                        setShowPopup(false); 
                        setNotificationCount(prev => Math.max(0, prev - 1));
                        fetch('clear_notification.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ order_id: notificationDetails.orderId }),
                            credentials: 'include'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) console.error('Error clearing notification:', data.message);
                        })
                        .catch(error => console.error('Error clearing notification:', error));
                    }} />}
                </nav>
            );
        }

        function NotificationsPopup({ details, onClose }) {
            return (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white p-6 rounded-lg shadow-xl w-full max-w-md notification-popup">
                        <h4 className="text-xl font-semibold text-green-800 mb-4">New Notification</h4>
                        <p className="text-gray-700 mb-4">{details.message}</p>
                        {details.orderId && <p className="text-gray-600 mb-4">Order ID: {details.orderId}</p>}
                        {details.status && <p className="text-gray-600 mb-4">Status: {details.status.charAt(0).toUpperCase() + details.status.slice(1)}</p>}
                        <button onClick={onClose} className="mt-4 bg-green-500 text-white px-6 py-2 rounded-full hover:bg-green-600 transition-all duration-300">Close</button>
                    </div>
                </div>
            );
        }

        function Hero() {
            return (
                <header id="home" className="relative h-screen bg-cover bg-center text-white flex items-center justify-center animate-panel" style={{ backgroundImage: `url('./assets/images/hero-bg.jpg'), linear-gradient(to bottom, #1a6141, #134d33)` }}>
                    <div className="absolute inset-0 bg-green-900 opacity-50"></div>
                    <div className="container mx-auto px-6 text-center relative z-10">
                        <h2 className="text-5xl md:text-7xl font-extrabold animate-text-up">Saththar Feeds</h2>
                        <p className="mt-6 text-xl md:text-3xl animate-text-up-delay max-w-3xl mx-auto">Discover Smart Pet Shop Solutions with AI-Powered Recommendations</p>
                        <a href="products.php" className="mt-10 inline-flex items-center bg-green-500 text-white px-10 py-4 rounded-full hover:bg-green-600 transition-all duration-300 font-semibold text-xl glass-btn animate-pulse-btn">Shop Now</a>
                    </div>
                </header>
            );
        }

        function ProductSpotlight({ setCartCount }) {
            const [products, setProducts] = React.useState([]);
            const [currentIndex, setCurrentIndex] = React.useState(0);
            const [error, setError] = React.useState('');
            const [cart, setCart] = React.useState(<?php echo json_encode(array_values($_SESSION['cart'])); ?>);

            React.useEffect(() => {
                setCartCount(Object.values(cart).reduce((sum, item) => sum + (item.quantity || 1), 0));
            }, [cart, setCartCount]);

            React.useEffect(() => {
                fetch('get_spotlight_products.php', { credentials: 'include' })
                    .then(response => response.json())
                    .then(data => data.success ? setProducts(data.products) : setError(data.message || 'Failed to load products.'))
                    .catch(error => { console.error('Error fetching products:', error); setError('Failed to load products. Check server connection.'); });
            }, []);

            React.useEffect(() => {
                if (products.length > 0) {
                    const interval = setInterval(() => setCurrentIndex((prev) => (prev + 1) % products.length), 5000);
                    return () => clearInterval(interval);
                }
            }, [products.length]);

            return (
                <section className="container mx-auto py-20 px-6 animate-panel">
                    <h3 className="text-4xl font-bold text-center mb-16 text-green-800">Featured Products</h3>
                    {error && <div className="text-red-600 text-center mb-4">{error}</div>}
                    {products.length > 0 ? (
                        <div className="relative overflow-hidden">
                            <div className="flex transition-transform duration-500 ease-in-out" style={{ transform: `translateX(-${currentIndex * 100}%)` }}>
                                {products.map((product, index) => (
                                    <div key={index} className="w-full flex-shrink-0">
                                        <div className="max-w-md mx-auto bg-white p-8 rounded-2xl shadow-xl card hover:shadow-2xl transition-shadow duration-300">
                                            {product.image && <img src={product.image} alt={product.name} className="product-image mb-6" onError={(e) => { e.target.src = 'http://localhost/saththar_feeds/uploads/products/default.jpg'; }} />}
                                            <h4 className="text-2xl font-semibold text-green-700 mb-3">{product.name}</h4>
                                            <p className="text-gray-600 mb-4">{product.description}</p>
                                            <p className="text-xl font-bold text-green-600">LKR {product.price}</p>
                                            <p className="text-gray-600 mt-2">Pet Type: {product.pet_type}</p>
                                            <p className="text-gray-600 mb-4">Pet Age: {product.pet_age}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <button className="absolute left-6 top-1/2 transform -translate-y-1/2 bg-green-500 text-white p-3 rounded-full hover:bg-green-600 transition-all duration-300" onClick={() => setCurrentIndex((prev) => (prev - 1 + products.length) % products.length)}><svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7"></path></svg></button>
                            <button className="absolute right-6 top-1/2 transform -translate-y-1/2 bg-green-500 text-white p-3 rounded-full hover:bg-green-600 transition-all duration-300" onClick={() => setCurrentIndex((prev) => (prev + 1) % products.length)}><svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path></svg></button>
                        </div>
                    ) : <p className="text-center text-gray-600">No products available.</p>}
                </section>
            );
        }

        function PetSelector({ setCartCount }) {
            const [petType, setPetType] = React.useState('');
            const [petAge, setPetAge] = React.useState('');
            const [recommendations, setRecommendations] = React.useState([]);
            const [cart, setCart] = React.useState(<?php echo json_encode(array_values($_SESSION['cart'])); ?>);
            const [isLoading, setIsLoading] = React.useState(false);
            const [showConfirm, setShowConfirm] = React.useState(false);
            const [error, setError] = React.useState('');

            React.useEffect(() => {
                setCartCount(Object.values(cart).reduce((sum, item) => sum + (item.quantity || 1), 0));
            }, [cart, setCartCount]);

            const handleSubmit = (e) => {
                e.preventDefault();
                if (!petType || !petAge) {
                    setError('Please select both pet type and age group.');
                    return;
                }
                setIsLoading(true);
                setError('');
                fetch('get_recommendation.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ animal: petType, age_range: petAge }),
                    credentials: 'include'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.products) {
                        setRecommendations(data.products);
                    } else {
                        setRecommendations([{ name: data.message || 'No recommendations available' }]);
                    }
                    setIsLoading(false);
                })
                .catch(error => {
                    console.error('Error fetching recommendations:', error);
                    setRecommendations([{ name: 'Failed to load recommendations' }]);
                    setError('Failed to connect to recommendation service.');
                    setIsLoading(false);
                });
            };

            const addToCart = (productId) => {
                fetch('cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add', product_id: productId, quantity: 1 }),
                    credentials: 'include'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const newCart = { ...cart };
                        if (!newCart[productId]) newCart[productId] = { id: productId, quantity: 0 };
                        newCart[productId].quantity += 1;
                        setCart(newCart);
                        setCartCount(Object.values(newCart).reduce((sum, item) => sum + item.quantity, 0));
                        alert('Product added to cart!');
                    } else {
                        alert('Failed to add item: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error adding to cart:', error);
                    alert('Error adding to cart. Check console for details.');
                });
            };

            const requestProducts = () => {
                if (Object.values(cart).length === 0) {
                    alert('Your cart is empty.');
                    return;
                }
                fetch('request_products.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ products: Object.values(cart).map(item => item.name) }),
                    credentials: 'include'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        setShowConfirm(true);
                        setCart({});
                        setCartCount(0);
                        fetch('update_cart.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ cart: {} }),
                            credentials: 'include'
                        }).catch(error => console.error('Error clearing cart:', error));
                        setTimeout(() => setShowConfirm(false), 3000);
                    } else {
                        alert(data.message || 'Failed to request products.');
                    }
                })
                .catch(error => console.error('Error:', error));
            };

            return (
                <section id="shop" className="container mx-auto py-20 px-6 animate-panel">
                    <h3 className="text-4xl font-bold text-center mb-16 text-green-800">Personalized Pet Products</h3>
                    {error && <div className="text-red-600 text-center mb-4">{error}</div>}
                    <form onSubmit={handleSubmit} className="max-w-md mx-auto bg-white p-10 rounded-2xl shadow-xl card">
                        <div className="mb-8">
                            <label htmlFor="petType" className="block text-sm font-medium text-gray-700 mb-3">Pet Type</label>
                            <select id="petType" value={petType} onChange={(e) => setPetType(e.target.value)} className="w-full p-4 border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-800" required aria-label="Select pet type">
                                <option value="">Choose a pet</option>
                                <option value="Cow">Cow</option>
                                <option value="Horse">Horse</option>
                                <option value="Sheep">Sheep</option>
                                <option value="Goat">Goat</option>
                                <option value="Hen">Hen</option>
                            </select>
                        </div>
                        <div className="mb-8">
                            <label htmlFor="petAge" className="block text-sm font-medium text-gray-700 mb-3">Pet Age Group</label>
                            <select id="petAge" value={petAge} onChange={(e) => setPetAge(e.target.value)} className="w-full p-4 border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-800" required aria-label="Select pet age group">
                                <option value="">Select Age Group</option>
                                <optgroup label="Cow">
                                    <option value="Calf (0–6 months)">Calf (0–6 months)</option>
                                    <option value="Heifer/Young Bull (6–24 months)">Heifer/Young Bull (6–24 months)</option>
                                    <option value="Mature Cow/Bull (2–8 years)">Mature Cow/Bull (2–8 years)</option>
                                    <option value="Senior (8+ years)">Senior (8+ years)</option>
                                </optgroup>
                                <optgroup label="Horse">
                                    <option value="Foal (0–6 months)">Foal (0–6 months)</option>
                                    <option value="Yearling (6–24 months)">Yearling (6–24 months)</option>
                                    <option value="Adult Horse (2–15 years)">Adult Horse (2–15 years)</option>
                                    <option value="Senior (15+ years)">Senior (15+ years)</option>
                                </optgroup>
                                <optgroup label="Sheep">
                                    <option value="Lamb (0–6 months)">Lamb (0–6 months)</option>
                                    <option value="Hogget (6–12 months)">Hogget (6–12 months)</option>
                                    <option value="Ewe/Ram (1–6 years)">Ewe/Ram (1–6 years)</option>
                                    <option value="Senior (6+ years)">Senior (6+ years)</option>
                                </optgroup>
                                <optgroup label="Goat">
                                    <option value="Kid (0–6 months)">Kid (0–6 months)</option>
                                    <option value="Doeling/Buckling (6–12 months)">Doeling/Buckling (6–12 months)</option>
                                    <option value="Adult Goat (1–7 years)">Adult Goat (1–7 years)</option>
                                    <option value="Senior (7+ years)">Senior (7+ years)</option>
                                </optgroup>
                                <optgroup label="Hen">
                                    <option value="Chick (0–6 weeks)">Chick (0–6 weeks)</option>
                                    <option value="Pullet/Cockerel (6–20 weeks)">Pullet/Cockerel (6–20 weeks)</option>
                                    <option value="Hen/Rooster (5–24 months+)">Hen/Rooster (5–24 months+)</option>
                                    <option value="Senior (2.5+ years)">Senior (2.5+ years)</option>
                                </optgroup>
                            </select>
                        </div>
                        <button type="submit" className="w-full bg-green-500 text-white p-4 rounded-full hover:bg-green-600 transition-all duration-300 font-medium text-lg" disabled={isLoading}>{isLoading ? 'Processing...' : 'Get Recommendations'}</button>
                    </form>
                    {recommendations.length > 0 && (
                        <div className="max-w-3xl mx-auto mt-12">
                            <h4 className="text-2xl font-semibold text-green-700 mb-8">Recommended Products</h4>
                            <ul className="space-y-4">
                                {recommendations.map((product, index) => (
                                    <li key={index} className="flex justify-between items-center bg-white p-6 rounded-lg shadow-md card hover:shadow-lg transition-shadow duration-300">
                                        <div className="flex items-center">
                                            <img src={product.image} alt={product.name} className="w-16 h-16 object-cover rounded mr-4" onError={(e) => { e.target.src = 'http://localhost/saththar_feeds/uploads/products/default.jpg'; }} />
                                            <div>
                                                <span className="text-gray-800 text-lg">{product.type === 'Food' ? 'Food:' : 'Vitamin:'} {product.name}</span>
                                                <p className="text-gray-600 text-sm">LKR {product.price}</p>
                                                <p className="text-gray-500 text-xs">Match: {product['Match Confidence (%)']}%</p>
                                            </div>
                                        </div>
                                        <button onClick={() => addToCart(product.id)} className="bg-green-700 text-white px-6 py-3 rounded-full hover:bg-green-800 transition-all duration-300 text-sm font-medium">Add to Cart</button>
                                    </li>
                                ))}
                            </ul>
                            {Object.values(cart).length > 0 && (
                                <div className="mt-8 text-center">
                                    <p className="text-gray-600 mb-6 text-lg">{Object.values(cart).map(item => item.name).join(' ')}</p>
                                    <button onClick={requestProducts} className="bg-green-500 text-white px-8 py-4 rounded-full hover:bg-green-600 transition-all duration-300 font-medium text-lg">Request Products</button>
                                </div>
                            )}
                        </div>
                    )}
                    {showConfirm && <div className="fixed top-6 right-6 bg-green-500 text-white p-6 rounded-lg shadow-xl animate-panel">Request sent successfully!</div>}
                </section>
            );
        }

        function Footer() {
            const [email, setEmail] = React.useState('');
            const [emailError, setEmailError] = React.useState('');

            const handleNewsletterSubmit = (e) => {
                e.preventDefault();
                if (!email.match(/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/)) {
                    setEmailError('Please enter a valid email.');
                    return;
                }
                fetch('newsletter.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email }),
                    credentials: 'include'
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message || 'Subscribed successfully!');
                    setEmail('');
                    setEmailError('');
                })
                .catch(error => console.error('Error:', error));
            };

            return (
                <footer className="bg-green-800 text-white py-16 animate-panel">
                    <div className="container mx-auto px-6">
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-12">
                            <div className="text-center md:text-left">
                                <h4 className="text-xl font-semibold mb-6">Saththar Feeds</h4>
                                <p className="text-sm text-gray-200">Smart Pet Shop Management for Your Animals</p>
                            </div>
                            <div className="text-center">
                                <h4 className="text-xl font-semibold mb-6">Connect With Us</h4>
                                <div className="flex justify-center space-x-6">
                                    <a href="#" className="hover:text-green-400 transition-colors duration-300" aria-label="Facebook"><svg className="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.563V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"></path></svg></a>
                                    <a href="#" className="hover:text-green-400 transition-colors duration-300" aria-label="Twitter"><svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"></path></svg></a>
                                    <a href="#" className="hover:text-green-400 transition-colors duration-300" aria-label="Contact"><svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></a>
                                </div>
                            </div>
                            <div className="text-center md:text-right">
                                <h4 className="text-xl font-semibold mb-6">Newsletter</h4>
                                <form onSubmit={handleNewsletterSubmit} className="flex justify-center md:justify-end">
                                    <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} placeholder="Enter your email" className="p-3 rounded-l-lg text-gray-900 bg-gray-100 border-none focus:ring-2 focus:ring-green-500 w-64" aria-label="Newsletter email" />
                                    <button type="submit" className="bg-green-500 text-white p-3 rounded-r-lg hover:bg-green-600 transition-all duration-300">Subscribe</button>
                                </form>
                                {emailError && <p className="text-red-300 text-sm mt-3">{emailError}</p>}
                            </div>
                        </div>
                        <p className="mt-12 text-center text-sm text-gray-300">© 2025 Saththar Feeds. All rights reserved. | Today: <?php echo $currentDateTime; ?></p>
                    </div>
                </footer>
            );
        }

        function App() {
            const username = <?php echo json_encode($username); ?>;
            const [cartCount, setCartCount] = React.useState(<?php echo count($_SESSION['cart']); ?>);
            const [notificationCount, setNotificationCount] = React.useState(0);

            return (
                <div>
                    <Navbar username={username} cartCount={cartCount} notificationCount={notificationCount} setNotificationCount={setNotificationCount} />
                    <Hero />
                    <ProductSpotlight setCartCount={setCartCount} />
                    <PetSelector setCartCount={setCartCount} />
                    <Footer />
                </div>
            );
        }

        try {
            ReactDOM.render(<App />, document.getElementById('root'));
        } catch (e) {
            console.error('React rendering error:', e);
            document.getElementById('error').textContent = 'Error rendering page. Check console for details.';
            document.getElementById('error').style.display = 'block';
        }
    </script>
</body>
</html>