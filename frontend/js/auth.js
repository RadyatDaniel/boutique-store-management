// ===================================
// AUTH.JS — Login Page Logic
// ===================================

document.addEventListener('DOMContentLoaded', () => {
  lucide.createIcons();

  document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const emailInput = document.getElementById('loginEmail').value;
    const passInput = document.getElementById('loginPassword').value;
    const btn = document.getElementById('loginBtn');
    const errorMsg = document.getElementById('errorMsg');

    // Simulate network request
    btn.innerHTML = 'Verifying...';
    btn.style.opacity = '0.6';
    btn.disabled = true;
    errorMsg.style.display = 'none';

    // Reset borders
    document.getElementById('loginEmail').style.borderColor = '';
    document.getElementById('loginPassword').style.borderColor = '';

    setTimeout(() => {
      const foundUser = window.MOCK_DATA.users.find(
        u => u.email === emailInput && u.password === passInput
      );

      if (foundUser) {
        localStorage.setItem('userRole', foundUser.role);
        localStorage.setItem('userName', foundUser.name);
        btn.innerHTML = '✓ Success';
        btn.style.background = 'var(--success)';

        setTimeout(() => {
          window.location.href = 'dashboard.html';
        }, 400);

      } else {
        btn.innerHTML = 'Log in';
        btn.style.opacity = '1';
        btn.disabled = false;
        errorMsg.style.display = 'block';
        document.getElementById('loginEmail').style.borderColor = 'var(--danger)';
        document.getElementById('loginPassword').style.borderColor = 'var(--danger)';
      }
    }, 600);
  });
});
