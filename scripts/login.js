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

    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault(); // Impede o envio padrão do formulário

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        // Clear previous error messages
        errorMessage.textContent = '';
        errorMessage.style.display = 'none';

        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: email,
                    senha: password
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Store user data in localStorage for use in other pages
                localStorage.setItem('userData', JSON.stringify(result.data));
                window.location.href = 'dashboard.html';
            } else {
                errorMessage.textContent = result.error || 'Erro no login. Tente novamente.';
                errorMessage.style.display = 'block';
            }
        } catch (error) {
            console.error('Login error:', error);
            errorMessage.textContent = 'Erro de conexão. Tente novamente.';
            errorMessage.style.display = 'block';
        }
    });
});
