document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('avaliacaoForm');
    const msg = document.getElementById('avaliacaoMsg');
    const backLink = document.querySelector('.back-link');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        msg.style.display = 'block';
        msg.textContent = 'Avaliação enviada com sucesso! Obrigado pelo feedback.';
        form.style.display = 'none';
        if (backLink) backLink.style.display = 'block';
    });
});
