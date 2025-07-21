document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btnNovoFrete').addEventListener('click', function() {
        window.location.href = 'tela_pesquisa.html';
    });
    document.getElementById('btnFreteAtual').addEventListener('click', function() {
        window.location.href = 'tela_frete.html';
    });
    document.getElementById('btnSair').addEventListener('click', function() {
        window.location.href = 'tela_login.html';
    });
});
