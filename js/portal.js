document.addEventListener('DOMContentLoaded', () => {
  console.log('Client dashboard loaded');

  // Logout functionality
  const logoutLink = document.querySelector('.logout-link');
  if (logoutLink) {
    logoutLink.addEventListener('click', function(e) {
      e.preventDefault();
      if (confirm('Are you sure you want to logout?')) {
        // In a real application, this would clear session and redirect
        window.location.href = 'login.html';
      }
    });
  }

  // Dashboard navigation active state
  const navLinks = document.querySelectorAll('.dashboard-nav a');
  navLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      if (this.getAttribute('href') === '#') {
        e.preventDefault();
      }
      navLinks.forEach(l => l.classList.remove('active'));
      this.classList.add('active');
    });
  });

  // Future: Load user data, case status, documents, messages from API
  // Example:
  // fetch('/api/dashboard')
  //   .then(response => response.json())
  //   .then(data => {
  //     // Update dashboard with real data
  //   });
});
