document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cadastroForm');
    const errorMessage = document.getElementById('errorMessage');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        errorMessage.style.display = 'none';
        errorMessage.textContent = '';

        const nome = document.getElementById('nome').value.trim();
        const email = document.getElementById('email').value.trim();
        const telefone = document.getElementById('telefone').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (!nome || !email || !telefone || !password || !confirmPassword) {
            errorMessage.textContent = 'Por favor, preencha todos os campos.';
            errorMessage.style.display = 'block';
            return;
        }
        if (password !== confirmPassword) {
            errorMessage.textContent = 'As senhas não coincidem.';
            errorMessage.style.display = 'block';
            return;
        }
        if (!/^\d{11}$/.test(telefone)) {
            errorMessage.textContent = 'Telefone deve conter 11 dígitos (DDD + número).';
            errorMessage.style.display = 'block';
            return;
        }
        // Aqui você pode adicionar a lógica de cadastro (ex: enviar para backend)
        alert('Cadastro realizado com sucesso!');
        window.location.href = 'tela_login.html';
    });
});
