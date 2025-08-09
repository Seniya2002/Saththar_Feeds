<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || !$_SESSION['admin_loggedin']) {
    header("Location: admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Saththar Feeds - Admin Panel">
    <title>Saththar Feeds - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="./assets/images/logo/image.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/react@17.0.2/umd/react.development.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/react-dom@17.0.2/umd/react-dom.development.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/babel-standalone@6.26.0/babel.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .green-gradient {
            background: linear-gradient(135deg, #15803d, #22c55e);
        }
        .hover-green-shadow:hover {
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);
        }
        .fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .table-container {
            max-height: 600px;
            overflow-y: auto;
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-container th, .table-container td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .table-container th {
            background-color: #f3f4f6;
            position: sticky;
            top: 0;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-white text-gray-900">
    <div id="root"></div>
    <div id="error" className="text-red-600 text-center p-4 hidden"></div>

    <script type="text/babel">
        function ErrorBoundary({ children }) {
            const [hasError, setHasError] = React.useState(false);

            React.useEffect(() => {
                const handleError = (error, errorInfo) => {
                    setHasError(true);
                    console.error('Error caught by boundary:', error, errorInfo);
                };
                window.addEventListener('error', handleError);
                return () => window.removeEventListener('error', handleError);
            }, []);

            if (hasError) {
                return <h2 className="text-center mt-20 text-red-600">Something went wrong. Please refresh the page or check the console.</h2>;
            }

            return children;
        }

        function Sidebar({ setActiveTab, activeTab }) {
            const handleLogout = () => {
                fetch('admin_logout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include'
                })
                .then(response => {
                    if (response.ok) {
                        window.location.href = 'admin_login.php';
                    } else {
                        throw new Error('Logout failed');
                    }
                })
                .catch(error => console.error('Logout error:', error));
            };

            return (
                <div className="fixed top-0 left-0 h-full w-64 bg-white border-r border-green-100 shadow-lg z-50">
                    <div className="p-6 flex items-center">
                        <img src="./assets/images/logo/feeds.png" alt="Saththar Feeds Logo" className="h-12 rounded-lg" />
                        <h1 className="ml-4 text-xl font-bold text-green-800">Admin Panel</h1>
                    </div>
                    <nav className="mt-6">
                        <button
                            onClick={() => setActiveTab('dashboard')}
                            className={`w-full text-left px-6 py-3 text-green-800 hover:bg-green-50 hover:text-green-600 transition-all duration-300 ${activeTab === 'dashboard' ? 'bg-green-50 text-green-600' : ''}`}
                        >
                            <ion-icon name="stats-chart-outline" className="mr-2"></ion-icon> Dashboard
                        </button>
                        <button
                            onClick={() => setActiveTab('products')}
                            className={`w-full text-left px-6 py-3 text-green-800 hover:bg-green-50 hover:text-green-600 transition-all duration-300 ${activeTab === 'products' ? 'bg-green-50 text-green-600' : ''}`}
                        >
                            <ion-icon name="cube-outline" className="mr-2"></ion-icon> Manage Products
                        </button>
                        <button
                            onClick={() => setActiveTab('requests')}
                            className={`w-full text-left px-6 py-3 text-green-800 hover:bg-green-50 hover:text-green-600 transition-all duration-300 ${activeTab === 'requests' ? 'bg-green-50 text-green-600' : ''}`}
                        >
                            <ion-icon name="person-outline" className="mr-2"></ion-icon> Customer Requests
                        </button>
                        <button
                            onClick={() => setActiveTab('transactions')}
                            className={`w-full text-left px-6 py-3 text-green-800 hover:bg-green-50 hover:text-green-600 transition-all duration-300 ${activeTab === 'transactions' ? 'bg-green-50 text-green-600' : ''}`}
                        >
                            <ion-icon name="cash-outline" className="mr-2"></ion-icon> Transactions
                        </button>
                        <button
                            onClick={() => setActiveTab('billing')}
                            className={`w-full text-left px-6 py-3 text-green-800 hover:bg-green-50 hover:text-green-600 transition-all duration-300 ${activeTab === 'billing' ? 'bg-green-50 text-green-600' : ''}`}
                        >
                            <ion-icon name="document-text-outline" className="mr-2"></ion-icon> Billing
                        </button>
                        <button
                            onClick={handleLogout}
                            className="w-full text-left px-6 py-3 text-red-600 hover:bg-red-50 hover:text-red-800 transition-all duration-300"
                        >
                            <ion-icon name="log-out-outline" className="mr-2"></ion-icon> Logout
                        </button>
                    </nav>
                </div>
            );
        }

        function Dashboard() {
            const [analytics, setAnalytics] = React.useState({ totalSales: 0, totalRequests: 0, pendingRequests: 0 });
            const [error, setError] = React.useState('');

            React.useEffect(() => {
                fetch('get_analytics.php', { credentials: 'include' })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            setAnalytics(data);
                            setError('');
                        } else {
                            setError(data.message || 'Failed to load analytics.');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching analytics:', error);
                        setError('Failed to load analytics. Check server connection.');
                    });
            }, []);

            return (
                <section className="ml-64 py-24 px-4 sm:px-6 lg:px-8 fade-in-up">
                    <h3 className="text-3xl font-bold text-green-800 mb-8">Dashboard</h3>
                    {error && <div className="text-red-600 text-center mb-4">{error}</div>}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div className="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-shadow duration-300">
                            <h4 className="text-xl font-semibold text-green-800 mb-2">Total Sales</h4>
                            <p className="text-3xl font-bold text-green-600">LKR {Number(analytics.totalSales).toFixed(2)}</p>
                        </div>
                        <div className="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-shadow duration-300">
                            <h4 className="text-xl font-semibold text-green-800 mb-2">Total Requests</h4>
                            <p className="text-3xl font-bold text-green-600">{analytics.totalRequests}</p>
                        </div>
                        <div className="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-shadow duration-300">
                            <h4 className="text-xl font-semibold text-green-800 mb-2">Pending Requests</h4>
                            <p className="text-3xl font-bold text-green-600">{analytics.pendingRequests}</p>
                        </div>
                    </div>
                </section>
            );
        }

        function ProductManagement() {
            const [products, setProducts] = React.useState([]);
            const [formData, setFormData] = React.useState({ id: '', name: '', description: '', price: '', stock: '', pet_type: '', pet_age: '', image: null });
            const [isEditing, setIsEditing] = React.useState(false);
            const [error, setError] = React.useState('');
            const [success, setSuccess] = React.useState('');

            React.useEffect(() => {
                fetch('get_products.php', { credentials: 'include' })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) setProducts(data.products);
                        else setError(data.message || 'Failed to load products.');
                    })
                    .catch(error => {
                        console.error('Error fetching products:', error);
                        setError('Failed to load products. Check server connection.');
                    });
            }, []);

            const handleInputChange = (e) => {
                const { name, value } = e.target;
                setFormData({ ...formData, [name]: value });
            };

            const handleFileChange = (e) => {
                setFormData({ ...formData, image: e.target.files[0] });
            };

            const validateForm = () => {
                if (!formData.name || !formData.description || !formData.price || !formData.stock || !formData.pet_type || !formData.pet_age) {
                    setError('All fields except image are required.');
                    return false;
                }
                if (isNaN(formData.price) || formData.price < 0 || isNaN(formData.stock) || formData.stock < 0) {
                    setError('Price and stock must be valid non-negative numbers.');
                    return false;
                }
                setError('');
                return true;
            };

            const handleSubmit = (e) => {
                e.preventDefault();
                if (!validateForm()) return;

                const form = new FormData();
                Object.keys(formData).forEach(key => {
                    if (key === 'image' && formData[key]) form.append(key, formData[key]);
                    else if (formData[key]) form.append(key, formData[key]);
                });

                const url = isEditing ? 'update_product.php' : 'add_product.php';
                fetch(url, {
                    method: 'POST',
                    body: form,
                    credentials: 'include'
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        fetch('get_products.php', { credentials: 'include' })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) setProducts(data.products);
                            });
                        setFormData({ id: '', name: '', description: '', price: '', stock: '', pet_type: '', pet_age: '', image: null });
                        setIsEditing(false);
                        setSuccess('Product saved successfully!');
                        setTimeout(() => setSuccess(''), 3000);
                    } else {
                        setError(data.message || 'Failed to save product.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    setError('An error occurred while saving the product.');
                });
            };

            const handleEdit = (product) => {
                setFormData({ ...product, image: null });
                setIsEditing(true);
            };

            const handleDelete = (id) => {
                if (window.confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                    fetch('delete_product.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id }),
                        credentials: 'include'
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            setProducts(products.filter(p => p.id !== id));
                            setSuccess('Product deleted successfully!');
                            setTimeout(() => setSuccess(''), 3000);
                        } else {
                            setError(data.message || 'Failed to delete product.');
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting product:', error);
                        setError('An error occurred while deleting the product.');
                    });
                }
            };

            return (
                <section className="ml-64 py-24 px-4 sm:px-6 lg:px-8 fade-in-up">
                    <h3 className="text-3xl font-bold text-green-800 mb-8">Manage Featured Products</h3>
                    {error && <div className="text-red-600 text-center mb-4">{error}</div>}
                    {success && <div className="text-green-600 text-center mb-4">{success}</div>}
                    <form onSubmit={handleSubmit} className="max-w-lg mx-auto bg-white p-8 rounded-2xl shadow-md hover:shadow-lg transition-shadow duration-300 mb-12">
                        {isEditing && <input type="hidden" name="id" value={formData.id} />}
                        <div className="mb-6">
                            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value={formData.name}
                                onChange={handleInputChange}
                                className="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="Enter product name"
                                required
                            />
                        </div>
                        <div className="mb-6">
                            <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea
                                id="description"
                                name="description"
                                value={formData.description}
                                onChange={handleInputChange}
                                className="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="Enter description"
                                required
                            ></textarea>
                        </div>
                        <div className="mb-6">
                            <label htmlFor="price" className="block text-sm font-medium text-gray-700 mb-2">Price (LKR)</label>
                            <input
                                type="number"
                                id="price"
                                name="price"
                                value={formData.price}
                                onChange={handleInputChange}
                                className="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="Enter price"
                                required
                                min="0"
                                step="0.01"
                            />
                        </div>
                        <div className="mb-6">
                            <label htmlFor="stock" className="block text-sm font-medium text-gray-700 mb-2">Stock Quantity</label>
                            <input
                                type="number"
                                id="stock"
                                name="stock"
                                value={formData.stock}
                                onChange={handleInputChange}
                                className="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="Enter stock"
                                required
                                min="0"
                            />
                        </div>
                        <div className="mb-6">
                            <label htmlFor="pet_type" className="block text-sm font-medium text-gray-700 mb-2">Pet Type</label>
                            <select
                                id="pet_type"
                                name="pet_type"
                                value={formData.pet_type}
                                onChange={handleInputChange}
                                className="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                required
                            >
                                <option value="">Select Pet Type</option>
                                <option value="Cow">Cow</option>
                                <option value="Horse">Horse</option>
                                <option value="Sheep">Sheep</option>
                                <option value="Goat">Goat</option>
                                <option value="Hen">Hen</option>
                            </select>
                        </div>
                        <div className="mb-6">
                            <label htmlFor="pet_age" className="block text-sm font-medium text-gray-700 mb-2">Pet Age Group</label>
                            <select
                                id="pet_age"
                                name="pet_age"
                                value={formData.pet_age}
                                onChange={handleInputChange}
                                className="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                required
                            >
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
                        <div className="mb-6">
                            <label htmlFor="image" className="block text-sm font-medium text-gray-700 mb-2">Product Image</label>
                            <input
                                type="file"
                                id="image"
                                name="image"
                                accept="image/*"
                                onChange={handleFileChange}
                                className="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            />
                            {formData.image && !isEditing && <p className="text-sm text-gray-600 mt-2">New image selected.</p>}
                        </div>
                        <button
                            type="submit"
                            className="w-full bg-white text-green-800 px-6 py-3 rounded-full border-2 border-green-800 hover:bg-green-800 hover:text-white hover-green-shadow transition-all duration-300"
                        >
                            {isEditing ? 'Update Product' : 'Add Product'}
                        </button>
                    </form>

                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        {products.map(product => (
                            <div key={product.id} className="max-w-sm mx-auto bg-white p-6 rounded-2xl shadow-md hover:shadow-2xl transition-shadow duration-300 card">
                                {product.image && <img src={product.image} alt={product.name} className="w-full h-48 object-cover rounded-lg mb-4" />}
                                <h4 className="text-2xl font-semibold text-green-700 mb-3">{product.name}</h4>
                                <p className="text-gray-600 mb-4">{product.description}</p>
                                <p className="text-xl font-bold text-green-600">LKR {product.price}</p>
                                <p className="text-gray-600 mt-2">Pet Type: {product.pet_type}</p>
                                <p className="text-gray-600 mb-4">Pet Age: {product.pet_age}</p>
                                <div className="flex space-x-4">
                                    <button
                                        onClick={() => handleEdit(product)}
                                        className="bg-white text-green-800 px-4 py-2 rounded-lg border-2 border-green-800 hover:bg-green-800 hover:text-white hover-green-shadow transition-all duration-300"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        onClick={() => handleDelete(product.id)}
                                        className="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-all duration-300"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                </section>
            );
        }

        function CustomerRequests() {
            const [requests, setRequests] = React.useState([]);
            const [error, setError] = React.useState('');
            const [success, setSuccess] = React.useState('');

            const fetchRequests = () => {
                fetch('get_requests.php', { credentials: 'include' })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success && Array.isArray(data.data)) {
                            setRequests(data.data);
                            if (data.data.length === 0) {
                                setError('No customer requests found.');
                            } else {
                                setError('');
                            }
                        } else {
                            setError(data.message || 'Failed to load customer requests.');
                            setRequests([]);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching requests:', error);
                        setError('Failed to load customer requests. Check server connection.');
                        setRequests([]);
                    });
            };

            React.useEffect(() => {
                fetchRequests();
            }, []);

            const handleProcessRequest = (requestId, status) => {
                fetch('process_request.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ request_id: requestId, status }),
                    credentials: 'include'
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        setSuccess(`Request #${requestId} ${status.toLowerCase()} successfully!`);
                        setTimeout(() => setSuccess(''), 3000);
                        fetchRequests();
                    } else {
                        setError(data.message || `Failed to ${status.toLowerCase()} request.`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    setError(`An error occurred while processing the request.`);
                });
            };

            return (
                <section className="ml-64 py-24 px-4 sm:px-6 lg:px-8 fade-in-up">
                    <div className="flex justify-between items-center mb-8">
                        <h3 className="text-3xl font-bold text-green-800">Customer Requests</h3>
                        <button
                            onClick={fetchRequests}
                            className="bg-white text-green-800 px-4 py-2 rounded-lg border-2 border-green-800 hover:bg-green-800 hover:text-white hover-green-shadow transition-all duration-300"
                        >
                            Refresh Requests
                        </button>
                    </div>
                    {error && <div className="text-red-600 text-center mb-4">{error}</div>}
                    {success && <div className="text-green-600 text-center mb-4">{success}</div>}
                    {requests.length === 0 && !error ? (
                        <p className="text-gray-600 text-center">No customer requests available.</p>
                    ) : (
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            {requests.map(request => (
                                <div key={request.id} className="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-shadow duration-300">
                                    <h4 className="text-xl font-semibold text-green-800 mb-2">Request #{request.id}</h4>
                                    <p className="text-gray-600 mb-2">Customer: {request.customer_name}</p>
                                    <p className="text-gray-600 mb-2">Product: {request.product_name}</p>
                                    <p className="text-gray-600 mb-2">Pet Type: {request.pet_type || 'N/A'}</p>
                                    <p className="text-gray-600 mb-2">Pet Age: {request.pet_age || 'N/A'}</p>
                                    <p className="text-gray-600 mb-2">Quantity: {request.quantity}</p>
                                    <p className="text-gray-600 mb-2">Amount: LKR {Number(request.amount).toFixed(2)}</p>
                                    <p className="text-gray-600 mb-4">Status: {request.status}</p>
                                    <div className="flex space-x-4">
                                        <button
                                            onClick={() => handleProcessRequest(request.id, 'Approved')}
                                            className="bg-white text-green-800 px-4 py-2 rounded-lg border-2 border-green-800 hover:bg-green-800 hover:text-white hover-green-shadow transition-all duration-300 disabled:bg-gray-400 disabled:text-gray-600 disabled:border-gray-400"
                                            disabled={request.status !== 'Pending'}
                                        >
                                            Approve
                                        </button>
                                        <button
                                            onClick={() => handleProcessRequest(request.id, 'Rejected')}
                                            className="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-all duration-300 disabled:bg-gray-400 disabled:text-gray-600"
                                            disabled={request.status !== 'Pending'}
                                        >
                                            Reject
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </section>
            );
        }

        function Transactions() {
            const [transactions, setTransactions] = React.useState([]);
            const [error, setError] = React.useState('');
            const [success, setSuccess] = React.useState('');

            const fetchTransactions = () => {
                fetch('get_transactions.php', { credentials: 'include' })
                    .then(response => {
                        console.log('Fetch response status:', response.status, response.statusText);
                        if (!response.ok) throw new Error('Network response was not ok: ' + response.statusText);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Transactions data:', data);
                        if (data.success && Array.isArray(data.data)) {
                            setTransactions(data.data);
                            if (data.data.length === 0) {
                                setError('No approved transactions found.');
                            } else {
                                setError('');
                            }
                        } else {
                            setError(data.message || 'Failed to load transactions.');
                            setTransactions([]);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching transactions:', error);
                        setError('Failed to load transactions: ' + error.message);
                        setTransactions([]);
                    });
            };

            React.useEffect(() => {
                fetchTransactions();
            }, []);

            return (
                <section className="ml-64 py-24 px-4 sm:px-6 lg:px-8 fade-in-up">
                    <div className="flex justify-between items-center mb-8">
                        <h3 className="text-3xl font-bold text-green-800">Transactions</h3>
                        <button
                            onClick={fetchTransactions}
                            className="bg-white text-green-800 px-4 py-2 rounded-lg border-2 border-green-800 hover:bg-green-800 hover:text-white hover-green-shadow transition-all duration-300"
                        >
                            Refresh Transactions
                        </button>
                    </div>
                    {error && <div className="text-red-600 text-center mb-4">{error}</div>}
                    {success && <div className="text-green-600 text-center mb-4">{success}</div>}
                    {transactions.length === 0 && !error ? (
                        <p className="text-gray-600 text-center">No approved transactions available.</p>
                    ) : (
                        <div className="table-container bg-white rounded-2xl shadow-md p-6">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Customer Name</th>
                                        <th>Product Name</th>
                                        <th>Pet Type</th>
                                        <th>Pet Age</th>
                                        <th>Quantity</th>
                                        <th>Amount (LKR)</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {transactions.map(transaction => (
                                        <tr key={transaction.id}>
                                            <td>{transaction.id}</td>
                                            <td>{transaction.customer_name}</td>
                                            <td>{transaction.product_name}</td>
                                            <td>{transaction.pet_type || 'N/A'}</td>
                                            <td>{transaction.pet_age || 'N/A'}</td>
                                            <td>{transaction.quantity}</td>
                                            <td>{Number(transaction.amount).toFixed(2)}</td>
                                            <td>{new Date(transaction.created_at).toLocaleString()}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </section>
            );
        }

        function Billing() {
            const [orders, setOrders] = React.useState([]);
            const [error, setError] = React.useState('');

            React.useEffect(() => {
                fetch('get_requests.php', { credentials: 'include' })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success && Array.isArray(data.data)) {
                            setOrders(data.data.filter(req => req.status === 'Approved'));
                            if (data.data.filter(req => req.status === 'Approved').length === 0) {
                                setError('No approved orders available.');
                            }
                        } else {
                            setError(data.message || 'Failed to load orders.');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching orders:', error);
                        setError('Failed to load orders. Check server connection.');
                    });
            }, []);

            const handleGenerateBill = (order) => {
                fetch('generate_bill.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        order_id: order.id,
                        product_name: order.product_name,
                        amount: order.amount,
                        customer_name: order.customer_name
                    }),
                    credentials: 'include'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('generate_bill.php response:', data);
                    if (data.success && data.pdf) {
                        try {
                            const byteCharacters = atob(data.pdf);
                            const byteNumbers = new Array(byteCharacters.length);
                            for (let i = 0; i < byteCharacters.length; i++) {
                                byteNumbers[i] = byteCharacters.charCodeAt(i);
                            }
                            const byteArray = new Uint8Array(byteNumbers);
                            const blob = new Blob([byteArray], { type: 'application/pdf' });
                            const url = window.URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.href = url;
                            link.setAttribute('download', `bill_${order.id}.pdf`);
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            window.URL.revokeObjectURL(url);
                            fetch('get_requests.php', { credentials: 'include' })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.success && Array.isArray(data.data)) {
                                        setOrders(data.data.filter(req => req.status === 'Approved'));
                                    }
                                });
                        } catch (e) {
                            console.error('Error processing PDF:', e);
                            setError('Failed to download PDF. Invalid PDF data.');
                        }
                    } else {
                        setError(data.message || 'Failed to generate bill. Check server response.');
                    }
                })
                .catch(error => {
                    console.error('Error generating bill:', error);
                    setError(`Failed to generate bill: ${error.message}. Please check the server connection or try again.`);
                });
            };

            return (
                <section className="ml-64 py-24 px-4 sm:px-6 lg:px-8 fade-in-up">
                    <h3 className="text-3xl font-bold text-green-800 mb-8">Generate Bills</h3>
                    {error && <div className="text-red-600 text-center mb-4">{error}</div>}
                    {orders.length === 0 && !error ? (
                        <p className="text-gray-600 text-center">No approved orders available.</p>
                    ) : (
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            {orders.map(order => (
                                <div key={order.id} className="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-shadow duration-300">
                                    <h4 className="text-xl font-semibold text-green-800 mb-2">Order #{order.id}</h4>
                                    <p className="text-gray-600 mb-2">Customer: {order.customer_name}</p>
                                    <p className="text-gray-600 mb-2">Product: {order.product_name}</p>
                                    <p className="text-gray-600 mb-2">Pet Type: {order.pet_type || 'N/A'}</p>
                                    <p className="text-gray-600 mb-4">Pet Age: {order.pet_age || 'N/A'}</p>
                                    <button
                                        onClick={() => handleGenerateBill(order)}
                                        className="bg-white text-green-800 px-4 py-2 rounded-lg border-2 border-green-800 hover:bg-green-800 hover:text-white hover-green-shadow transition-all duration-300"
                                    >
                                        Generate Bill
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}
                </section>
            );
        }

        function AdminPanel() {
            const [activeTab, setActiveTab] = React.useState('dashboard');

            return (
                <ErrorBoundary>
                    <div className="flex">
                        <Sidebar setActiveTab={setActiveTab} activeTab={activeTab} />
                        <div className="flex-1">
                            {activeTab === 'dashboard' && <Dashboard />}
                            {activeTab === 'products' && <ProductManagement />}
                            {activeTab === 'requests' && <CustomerRequests />}
                            {activeTab === 'transactions' && <Transactions />}
                            {activeTab === 'billing' && <Billing />}
                        </div>
                    </div>
                </ErrorBoundary>
            );
        }

        try {
            ReactDOM.render(<AdminPanel />, document.getElementById('root'));
        } catch (e) {
            console.error('React rendering error:', e);
            document.getElementById('error').style.display = 'block';
        }
    </script>
</body>
</html>