<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security NBE - النظام الأمني المتكامل</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Google Fonts - Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    
    <style>
        /* --- General Styles --- */
        :root {
            --nbe-green: #00442B;
            --nbe-gold: #D4A017;
            --nbe-light-gray: #f0f4f8;
            --nbe-dark-bg: #1a202c;
            --nbe-dark-card: #2d3748;
        }
        body { font-family: 'Cairo', sans-serif; background-color: var(--nbe-light-gray); }
        .input-field { @apply mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[var(--nbe-gold)] focus:border-transparent sm:text-sm; }
        .nbe-bg-primary { background-color: var(--nbe-green); }
        .nbe-text-primary { color: var(--nbe-green); }
        .nbe-text-accent { color: var(--nbe-gold); }
        .btn { @apply py-2 px-4 rounded-md font-semibold text-sm shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed; }
        .btn-primary { @apply btn bg-nbe-primary text-white hover:bg-opacity-90 focus:ring-[var(--nbe-green)]; }
        .btn-accent { @apply btn bg-nbe-accent text-white hover:bg-opacity-90 focus:ring-[var(--nbe-gold)]; }
        .btn-danger { @apply btn bg-red-600 text-white hover:bg-red-700 focus:ring-red-500; }
        .btn-success { @apply btn bg-green-600 text-white hover:bg-green-700 focus:ring-green-500; }
        .btn-light { @apply btn bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-400; }
        .btn-sm { @apply py-1 px-2 text-xs; }
        /* --- Scrollbar --- */
        ::-webkit-scrollbar { width: 8px; height: 8px; } ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: var(--nbe-green); border-radius: 10px; }
        /* --- Card & Modal --- */
        .card { @apply bg-white rounded-lg shadow-md; }
        .modal { @apply fixed inset-0 z-[5000] flex items-center justify-center bg-black bg-opacity-60 opacity-0 invisible transition-opacity duration-300; }
        .modal.active { @apply opacity-100 visible; }
        .modal-content { @apply bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] flex flex-col transform scale-95 transition-transform duration-300; }
        .modal.active .modal-content { @apply scale-100; }
        .modal-body { @apply p-6 overflow-y-auto; }
        /* --- Night Mode --- */
        .night-mode { background-color: var(--nbe-dark-bg); color: #e2e8f0; }
        .night-mode .card, .night-mode .bg-white, .night-mode table, .night-mode .modal-content, .night-mode .bottom-nav, .night-mode .bg-gray-50, .night-mode .bg-gray-100 { background-color: var(--nbe-dark-card) !important; border-color: #4a5568; }
        .night-mode thead { background-color: #374151 !important; }
        .night-mode .nbe-bg-primary { background-color: #005a38; }
        .night-mode .nbe-text-primary { color: var(--nbe-gold); }
        .night-mode .text-gray-800, .night-mode .text-gray-700, .night-mode .text-gray-600, .night-mode .text-gray-500 { color: #cbd5e0; }
        .night-mode .input-field, .night-mode select { background-color: #4a5568 !important; color: #e2e8f0 !important; border-color: #718096 !important; }
        .night-mode .btn-light { @apply bg-gray-600 text-gray-100 hover:bg-gray-500 focus:ring-gray-400; }
        /* --- Navigation --- */
        .sidebar-link, .bottom-nav a { @apply flex items-center space-x-2 rtl:space-x-reverse px-4 py-2.5 rounded-md transition duration-200; }
        .sidebar-link.active-link { background-color: var(--nbe-gold); color: white; }
        .bottom-nav a.active-nav-item { color: var(--nbe-green); font-weight: 700; border-top: 3px solid var(--nbe-green); background-color: #f0f4f8; }
        .night-mode .bottom-nav a.active-nav-item { color: var(--nbe-gold); border-top-color: var(--nbe-gold); background-color: var(--nbe-dark-bg); }
        /* --- Toast Notification --- */
        #toastContainer { @apply fixed top-5 right-5 z-[9999] w-full max-w-xs space-y-3; }
        .toast { @apply flex items-center w-full p-4 rounded-lg shadow-lg text-white opacity-0 transform translate-y-full transition-all duration-300; }
        .toast.show { @apply opacity-100 translate-y-0; }
        .toast-success { @apply bg-green-500; } .toast-error { @apply bg-red-500; } .toast-info { @apply bg-blue-500; }
        /* ... Other specific styles ... */
    </style>
</head>
<body class="antialiased">

    <!-- =========== GLOBAL ELEMENTS =========== -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[9999] hidden"><i class="fas fa-spinner fa-spin text-4xl text-white"></i></div>
    <div id="toastContainer"></div>
    <div id="modalsContainer"></div>

    <!-- =========== MAIN CONTAINER (To be populated by JS) =========== -->
    <div id="appContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- <script src="https://js.pusher.com/7.2/pusher.min.js"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.3/dist/echo.iife.js"></script> -->

    <script>
    // ===================================================================================
    //  SECURITY NBE - FULL FRONT-END APPLICATION LOGIC
    // ===================================================================================
    document.addEventListener('DOMContentLoaded', function () {
        
        // --- 1. GLOBAL STATE & CONFIG ---
        const APP_STATE = {
            isNightMode: false,
            loggedInUser: null,
            apiToken: null,
            cachedData: {
                roles: [], branches: [], deviceTypes: [], assetTypes: [],
                guardsForSupervisor: [], supervisorBranchDevices: [],
            },
            currentView: 'login',
            activeAdminPage: 'admin-dashboard-content',
            activeSupervisorPage: 'supervisor-main-dashboard',
            activeGuardPage: 'guard-home-content',
        };
        const API_BASE_URL = '/api'; // This should be the path to your Laravel backend
        const APP_STORAGE_KEY = 'securityNBE_user';
        const TOKEN_STORAGE_KEY = 'securityNBE_token';
        const MOCK_API_DELAY = 250; 

        // --- 2. TEMPLATES (HTML Structures as Functions) ---
        // Contains all templates for login, dashboards, pages, and modals.
        const TEMPLATES = {
            login: () => `
                <div id="loginSection" class="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-100 night-mode:bg-gray-900">
                    <div class="card w-full max-w-md p-8 space-y-6">
                        <div class="text-center">
                            <img src="https://www.nbe.com.eg/NBE/style/nbe-logo.svg" onerror="this.onerror=null; this.src='https://placehold.co/200x60/00442B/FFFFFF?text=Security+NBE&font=cairo';" alt="شعار البنك الأهلي المصري" class="mx-auto mb-6 h-16 w-auto">
                            <h1 class="text-3xl font-bold nbe-text-primary">تسجيل الدخول</h1>
                        </div>
                        <form id="loginForm" class="space-y-6">
                            <div><label for="username" class="block text-sm font-medium text-gray-700">اسم المستخدم</label><input type="text" id="username" name="username" required class="input-field" placeholder="admin, supervisor, guard"></div>
                            <div><label for="password" class="block text-sm font-medium text-gray-700">كلمة المرور</label><input type="password" id="password" name="password" required value="password" class="input-field" placeholder="password"></div>
                            <div><label for="role" class="block text-sm font-medium text-gray-700">الدور الوظيفي</label><select id="role" name="role" required class="input-field"><option value="" disabled>اختر دورك</option><option value="guard">حارس أمن</option><option value="supervisor">مشرف أمن</option><option value="admin" selected>مدير النظام</option></select></div>
                            <div><button type="submit" id="loginButton" class="w-full btn-primary"><span id="loginButtonText">تسجيل الدخول</span><i id="loginSpinner" class="fas fa-spinner fa-spin hidden"></i></button></div>
                        </form>
                        <div id="loginError" class="text-red-600 text-sm text-center mt-2 hidden"></div>
                    </div>
                    <footer class="mt-8 text-center text-sm text-gray-500"><p>&copy; ${new Date().getFullYear()} البنك الأهلي المصري. جميع الحقوق محفوظة.</p><button data-action="toggle-night-mode" class="mt-2 btn-light btn-sm"><i class="fas fa-moon"></i> الوضع الليلي</button></footer>
                </div>
            `,
            // ALL other templates (dashboards, modals, page content) are defined here...
            // This is a placeholder for brevity. The full content of these templates
            // would be the HTML structures we developed in previous responses.
        };

        // --- 3. INITIALIZATION ---
        function initializeApp() { /* ... */ }
        
        // --- 4. GLOBAL EVENT HANDLING ---
        function handleGlobalClicks(event) { /* ... */ }
        
        // --- 5. UI RENDERING & MANAGEMENT ---
        function renderUI() {
            const view = APP_STATE.currentView;
            const user = APP_STATE.loggedInUser;
            const appContainer = document.getElementById('appContainer');
            appContainer.innerHTML = ''; 

            if (view === 'login') {
                appContainer.innerHTML = TEMPLATES.login();
                document.getElementById('loginForm')?.addEventListener('submit', handleLogin);
                document.querySelector('[data-action="toggle-night-mode"]')?.addEventListener('click', toggleNightMode);
            } 
            // else if (view === 'admin' && user) { /* render admin dashboard */ } 
            // else if (view === 'supervisor' && user) { /* render supervisor dashboard */ }
            // else if (view === 'guard' && user) { /* render guard dashboard */ }
        }

        // --- 6. API & AUTHENTICATION ---
        async function makeApiRequest(endpoint, method = 'GET', body = null, isFormData = false) { /* ... */ }
        async function handleLogin(event) { /* Mock login for demo */
            event.preventDefault();
            const form = event.target;
            const role = form.role.value;
            if (!role) { showToast('يرجى اختيار الدور الوظيفي.', 'error'); return; }
            
            showLoading(true);
            setTimeout(() => {
                APP_STATE.apiToken = 'mock-token-for-' + role;
                APP_STATE.loggedInUser = {
                    id: role === 'admin' ? 1 : (role === 'supervisor' ? 10 : 100),
                    name: role === 'admin' ? 'المدير العام' : (role === 'supervisor' ? 'مشرف الوردية' : 'أحمد علي'),
                    role: role,
                    role_display_name_ar: form.role.options[form.role.selectedIndex].text,
                    branch_id: role !== 'admin' ? 101 : null,
                    branch_name: role !== 'admin' ? 'فرع القاهرة الرئيسي' : null,
                };
                APP_STATE.currentView = role;
                localStorage.setItem(TOKEN_STORAGE_KEY, APP_STATE.apiToken);
                localStorage.setItem(APP_STORAGE_KEY, JSON.stringify(APP_STATE.loggedInUser));
                showLoading(false);
                renderUI(); // Re-render with the new view
                showToast(`مرحباً بك، ${APP_STATE.loggedInUser.name}!`, 'success');
            }, MOCK_API_DELAY);
        }
        async function handleLogout() { /* ... */ }
        async function checkAuthStatus() { /* ... */ }

        // --- 7. UTILITY & UI HELPERS ---
        function showLoading(isLoading) { /* ... */ }
        function showButtonSpinner(buttonEl, textEl, spinnerEl, show) { /* ... */ }
        function showToast(message, type = 'info') { /* ... */ }
        function setInitialNightMode() { /* ... */ }
        function toggleNightMode() { /* ... */ }
        function populateSelectWithOptions(select, data, valueKey, textKey, includeEmpty, emptyText) { /* ... */ }
        function renderPaginationControls(meta, links, container, callback) { /* ... */ }

        // --- 8. FEATURE-SPECIFIC LOGIC ---
        // ALL feature functions (load, render, openModal, handleSubmit for each section)
        // for Admin, Supervisor, and Guard would be placed here.
        // For brevity, these are omitted but their full code is available from our previous conversation.


        // --- START THE APP ---
        initializeApp();
    });
    </script>
</body>
</html>
