document.addEventListener('DOMContentLoaded', async function() {
    // Check if user is logged in
    const userData = localStorage.getItem('userData');
    if (!userData) {
        window.location.href = 'tela_login.html';
        return;
    }

    const user = JSON.parse(userData);
    
    // Try to get current freight ID or find user's active freight
    let currentFreteId = localStorage.getItem('currentFreteId');
    
    if (!currentFreteId) {
        // If no specific freight ID, try to find user's active freight
        await findActiveFreight(user.id_usu);
    } else {
        await loadFreightDetails(currentFreteId);
    }

    document.getElementById('btnChat').addEventListener('click', function() {
        window.location.href = 'tela_chat.html';
    });
    
    document.getElementById('btnFinalizar').addEventListener('click', async function() {
        if (currentFreteId) {
            await finalizeFreight(currentFreteId);
        } else {
            alert('Nenhum frete ativo para finalizar.');
        }
    });

    async function findActiveFreight(userId) {
        try {
            const response = await fetch(`/api/frete?id_cliente=${userId}`);
            const result = await response.json();

            if (response.ok && result.success && result.data.length > 0) {
                // Find the most recent active freight
                const activeFreights = result.data.filter(f => 
                    f.status !== 'concluido' && f.status !== 'cancelado'
                );
                
                if (activeFreights.length > 0) {
                    currentFreteId = activeFreights[0].id_frete;
                    localStorage.setItem('currentFreteId', currentFreteId);
                    await loadFreightDetails(currentFreteId);
                } else {
                    showNoActiveFreight();
                }
            } else {
                showNoActiveFreight();
            }
        } catch (error) {
            console.error('Error finding active freight:', error);
            showNoActiveFreight();
        }
    }

    async function loadFreightDetails(freteId) {
        try {
            const response = await fetch(`/api/frete/${freteId}`);
            const result = await response.json();

            if (response.ok && result.success) {
                const frete = result.data;
                
                document.getElementById('origem').textContent = frete.end_origem;
                document.getElementById('destino').textContent = frete.end_destino;
                document.getElementById('motorista').textContent = 
                    frete.fretista_nome || 'Aguardando motorista...';
                document.getElementById('status').textContent = getStatusDisplay(frete.status);
                
                // Update finalize button based on status
                const btnFinalizar = document.getElementById('btnFinalizar');
                if (frete.status === 'concluido') {
                    btnFinalizar.textContent = 'Avaliar Motorista';
                    btnFinalizar.onclick = () => {
                        localStorage.setItem('freteParaAvaliacao', freteId);
                        window.location.href = 'tela_avaliacao.html';
                    };
                } else if (frete.status === 'em andamento') {
                    btnFinalizar.textContent = 'Finalizar Frete';
                } else {
                    btnFinalizar.textContent = 'Aguardando...';
                    btnFinalizar.disabled = true;
                }
                
            } else {
                showNoActiveFreight();
            }
        } catch (error) {
            console.error('Error loading freight details:', error);
            showNoActiveFreight();
        }
    }

    async function finalizeFreight(freteId) {
        try {
            const response = await fetch(`/api/frete/${freteId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    status: 'concluido'
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert('Frete finalizado com sucesso!');
                localStorage.setItem('freteParaAvaliacao', freteId);
                window.location.href = 'tela_avaliacao.html';
            } else {
                alert('Erro ao finalizar frete: ' + result.error);
            }
        } catch (error) {
            console.error('Error finalizing freight:', error);
            alert('Erro de conexão ao finalizar frete.');
        }
    }

    function showNoActiveFreight() {
        document.getElementById('origem').textContent = 'Nenhum frete ativo';
        document.getElementById('destino').textContent = '-';
        document.getElementById('motorista').textContent = '-';
        document.getElementById('status').textContent = 'Sem frete ativo';
        
        const btnFinalizar = document.getElementById('btnFinalizar');
        btnFinalizar.textContent = 'Criar Novo Frete';
        btnFinalizar.onclick = () => window.location.href = 'tela_pesquisa.html';
    }

    function getStatusDisplay(status) {
        const statusMap = {
            'pendente': 'Aguardando motorista',
            'aceito': 'Motorista a caminho para retirada',
            'em andamento': 'Frete em andamento',
            'concluido': 'Frete concluído',
            'cancelado': 'Frete cancelado'
        };
        return statusMap[status] || status;
    }
});
