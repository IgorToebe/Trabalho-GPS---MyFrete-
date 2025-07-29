document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in
    const userData = localStorage.getItem('userData');
    if (!userData) {
        window.location.href = 'tela_login.html';
        return;
    }

    const user = JSON.parse(userData);
    
    // Display welcome message with user name
    const welcomeElement = document.querySelector('h3');
    if (welcomeElement) {
        welcomeElement.textContent = `Bem-vindo, ${user.nomecompleto}!`;
    }

    document.getElementById('btnNovoFrete').addEventListener('click', function() {
        window.location.href = 'tela_pesquisa.html';
    });
    
    document.getElementById('btnFreteAtual').addEventListener('click', function() {
        window.location.href = 'tela_frete.html';
    });
    
    document.getElementById('btnSair').addEventListener('click', function() {
        // Clear user data from localStorage
        localStorage.removeItem('userData');
        window.location.href = 'tela_login.html';
    });
});
