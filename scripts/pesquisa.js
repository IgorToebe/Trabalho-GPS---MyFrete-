document.addEventListener('DOMContentLoaded', function() {
    const pesquisaForm = document.getElementById('pesquisaForm');
    const resultado = document.getElementById('resultadoPesquisa');
    let btnRealizar = null;

    pesquisaForm.addEventListener('submit', function(e) {
        e.preventDefault();
        resultado.style.display = 'block';
        resultado.textContent = 'Buscando motoristas disponíveis...';
        pesquisaForm.querySelectorAll('input, textarea, button').forEach(el => el.disabled = true);
        setTimeout(() => {
            pesquisaForm.style.display = 'none';
            resultado.innerHTML = '<strong>Motorista encontrado!</strong><br>Nome: João Silva<br><br>';
            btnRealizar = document.createElement('button');
            btnRealizar.textContent = 'Escolher Motorista';
            btnRealizar.style.marginTop = '16px';
            resultado.appendChild(btnRealizar);
            btnRealizar.addEventListener('click', function() {
                window.location.href = 'tela_frete.html';
            });
        }, 1200);
    });
});
