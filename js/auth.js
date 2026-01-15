function loginUser() {
  const emailInput = document.querySelector('.auth-box input[type="email"]');
  const passwordInput = document.querySelector('.auth-box input[type="password"]');
  
  const email = emailInput?.value.trim();
  const password = passwordInput?.value;

  if (!email || !password) {
    alert('Please enter both email and password');
    return;
  }

  // Basic email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert('Please enter a valid email address');
    return;
  }

  // In a real application, this would authenticate with a backend
  // Example API call:
  // fetch('/api/auth/login', {
  //   method: 'POST',
  //   headers: { 'Content-Type': 'application/json' },
  //   body: JSON.stringify({ email, password })
  // })
  // .then(response => response.json())
  // .then(data => {
  //   if (data.success) {
  //     localStorage.setItem('token', data.token);
  //     window.location.href = 'dashboard.html';
  //   } else {
  //     alert(data.message || 'Login failed');
  //   }
  // })
  // .catch(error => {
  //   alert('An error occurred. Please try again.');
  // });

  // For demo purposes, show message and redirect
  console.log('Login attempt:', email);
  alert('Login functionality will be connected to backend authentication system.\n\nFor demo: Redirecting to dashboard...');
  
  // Demo redirect (remove in production)
  setTimeout(() => {
    window.location.href = 'dashboard.html';
  }, 500);
}

// Add enter key support for login form
document.addEventListener('DOMContentLoaded', function() {
  const passwordInput = document.querySelector('.auth-box input[type="password"]');
  if (passwordInput) {
    passwordInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        loginUser();
      }
    });
  }
});
