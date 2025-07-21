document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('errorMessage');

    // Integração: clique no link de cadastro
    const signupLink = document.querySelector('.signup-link a');
    if (signupLink) {
        signupLink.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'tela_cadastro.html';
        });
    }

    loginForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Impede o envio padrão do formulário

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        // Validação simples (apenas para demonstração)
        if (email === 'teste@myfrete.com' && password === '12345') {
            errorMessage.textContent = '';
            errorMessage.style.display = 'none';
            window.location.href = 'dashboard.html';
        } else {
            errorMessage.textContent = 'E-mail ou senha incorretos. Tente novamente.';
            errorMessage.style.display = 'block';
        }
    });
});
