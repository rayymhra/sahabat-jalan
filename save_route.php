// Authentication functions
function login(usernameEmail, password) {
    return fetch('api/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            username_email: usernameEmail,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update UI with user info
            updateUserUI(data.data.user);
            return data;
        } else {
            throw new Error(data.message);
        }
    });
}

function register(name, email, password) {
    return fetch('api/register.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            name: name,
            email: email,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update UI with user info
            updateUserUI(data.data.user);
            return data;
        } else {
            throw new Error(data.message);
        }
    });
}

function logout() {
    return fetch('logout.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(() => {
        // Clear user info from UI
        clearUserUI();
        window.location.reload();
    });
}

function updateUserUI(user) {
    // Update navigation with user info
    const loginBtn = document.getElementById('loginBtn');
    const registerBtn = document.getElementById('registerBtn');
    
    if (loginBtn && registerBtn) {
        loginBtn.style.display = 'none';
        registerBtn.style.display = 'none';
        
        // Create user dropdown
        const userNav = `
            <div class="dropdown">
                <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <img src="${user.avatar}" width="24" height="24" class="rounded-circle me-2">
                    ${user.name}
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                    <li><a class="dropdown-item" href="my_reports.php">My Reports</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="logout()">Logout</a></li>
                </ul>
            </div>
        `;
        
        // Add user dropdown to navigation
        registerBtn.insertAdjacentHTML('afterend', userNav);
    }
    
    // Enable report functionality
    document.getElementById('addReportBtn').disabled = false;
}

function clearUserUI() {
    // Reset navigation to login/register buttons
    const loginBtn = document.getElementById('loginBtn');
    const registerBtn = document.getElementById('registerBtn');
    const userDropdown = document.querySelector('.dropdown');
    
    if (userDropdown) {
        userDropdown.remove();
    }
    
    if (loginBtn && registerBtn) {
        loginBtn.style.display = 'block';
        registerBtn.style.display = 'block';
    }
    
    // Disable report functionality
    document.getElementById('addReportBtn').disabled = true;
}

// Check authentication status on page load
function checkAuthStatus() {
    fetch('api/check_auth.php', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.data.user) {
            updateUserUI(data.data.user);
        } else {
            clearUserUI();
        }
    })
    .catch(() => {
        clearUserUI();
    });
}

// Add this to your main initialization
document.addEventListener('DOMContentLoaded', function() {
    checkAuthStatus();
});