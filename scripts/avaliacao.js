document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in
    const userData = localStorage.getItem('userData');
    if (!userData) {
        window.location.href = 'tela_login.html';
        return;
    }

    const form = document.getElementById('avaliacaoForm');
    const msg = document.getElementById('avaliacaoMsg');
    const backLink = document.querySelector('.back-link');
    
    // Get freight ID for evaluation
    const freteParaAvaliacao = localStorage.getItem('freteParaAvaliacao');
    
    if (!freteParaAvaliacao) {
        msg.style.display = 'block';
        msg.innerHTML = '<div style="color: red;">Nenhum frete para avaliar.</div>';
        form.style.display = 'none';
        return;
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const nota = document.getElementById('nota').value;
        const comentario = document.getElementById('comentario').value.trim();
        
        if (!nota) {
            alert('Por favor, selecione uma nota.');
            return;
        }

        try {
            const response = await fetch('/api/frete_avaliacao', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_frete: freteParaAvaliacao,
                    nota: parseInt(nota),
                    comentario: comentario || null
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                msg.style.display = 'block';
                msg.innerHTML = '<div style="color: green;">✅ Avaliação enviada com sucesso! Obrigado pelo feedback.</div>';
                form.style.display = 'none';
                
                // Clear the freight evaluation data
                localStorage.removeItem('freteParaAvaliacao');
                localStorage.removeItem('currentFreteId');
                
                if (backLink) backLink.style.display = 'block';
            } else {
                msg.style.display = 'block';
                msg.innerHTML = `<div style="color: red;">❌ Erro: ${result.error}</div>`;
            }
        } catch (error) {
            console.error('Error submitting evaluation:', error);
            msg.style.display = 'block';
            msg.innerHTML = '<div style="color: red;">❌ Erro de conexão. Tente novamente.</div>';
        }
    });
});
