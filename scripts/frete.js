document.addEventListener('DOMContentLoaded', async function () {
    // Check if user is logged in
    const userData = localStorage.getItem('userData');
    if (!userData) {
        window.location.href = 'tela_login.html';
        return;
    }

    const user = JSON.parse(userData);

    // Try to get current freight ID or find user's active freight
    let currentFreteId = localStorage.getItem('currentFreteId');
    let statusTimer = null;
    let currentStatusIndex = 0;
    const statusKeys = ['pendente', 'aceito', 'em andamento', 'concluido'];

    if (!currentFreteId) {
        // If no specific freight ID, try to find user's active freight
        await findActiveFreight(user.id_usu);
    } else {
        await loadFreightDetails(currentFreteId);
    }

    document.getElementById('btnChat').addEventListener('click', function () {
        window.location.href = 'tela_chat.html';
    });

    document.getElementById('btnFinalizar').addEventListener('click', async function () {
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

                // Start status cycling
                startStatusCycling(frete.status);

            } else {
                showNoActiveFreight();
            }
        } catch (error) {
            console.error('Error loading freight details:', error);
            showNoActiveFreight();
        }
    }

    function startStatusCycling(initialStatus) {
        // Find the starting index based on current status
        currentStatusIndex = statusKeys.findIndex(status => status === initialStatus);
        if (currentStatusIndex === -1) currentStatusIndex = 0;

        // Update status display immediately
        updateStatusDisplay(statusKeys[currentStatusIndex]);

        // Clear any existing timer
        if (statusTimer) {
            clearInterval(statusTimer);
        }

        // Start cycling through statuses every 5 seconds
        statusTimer = setInterval(() => {
            currentStatusIndex = (currentStatusIndex + 1) % statusKeys.length;
            const newStatus = statusKeys[currentStatusIndex];
            updateStatusDisplay(newStatus);
            updateButtonForStatus(newStatus);
        }, 5000);
    }

    function updateStatusDisplay(status) {
        const statusElement = document.getElementById('status');
        const displayText = getStatusDisplay(status);
        statusElement.textContent = displayText;
    }

    function updateButtonForStatus(status) {
        const btnFinalizar = document.getElementById('btnFinalizar');

        switch (status) {
            case 'pendente':
                btnFinalizar.textContent = 'Aguardando motorista...';
                btnFinalizar.disabled = true;
                btnFinalizar.style.backgroundColor = '#ccc';
                break;
            case 'aceito':
                btnFinalizar.textContent = 'Aguardando retirada...';
                btnFinalizar.disabled = true;
                btnFinalizar.style.backgroundColor = '#ffa500';
                break;
            case 'em andamento':
                btnFinalizar.textContent = 'Finalizar Frete';
                btnFinalizar.disabled = false;
                btnFinalizar.style.backgroundColor = '#28a745';
                btnFinalizar.onclick = () => finalizeFreight(currentFreteId);
                break;
            case 'concluido':
                btnFinalizar.textContent = 'Avaliar Motorista';
                btnFinalizar.disabled = false;
                btnFinalizar.style.backgroundColor = '#007bff';
                btnFinalizar.onclick = () => {
                    localStorage.setItem('freteParaAvaliacao', currentFreteId);
                    window.location.href = 'tela_avaliacao.html';
                };
                break;
            default:
                btnFinalizar.textContent = 'Aguardando...';
                btnFinalizar.disabled = true;
                btnFinalizar.style.backgroundColor = '#ccc';
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
        // Clear status timer if running
        if (statusTimer) {
            clearInterval(statusTimer);
            statusTimer = null;
        }

        document.getElementById('origem').textContent = 'Nenhum frete ativo';
        document.getElementById('destino').textContent = '-';
        document.getElementById('motorista').textContent = '-';
        document.getElementById('status').textContent = 'Sem frete ativo';

        const btnFinalizar = document.getElementById('btnFinalizar');
        btnFinalizar.textContent = 'Criar Novo Frete';
        btnFinalizar.disabled = false;
        btnFinalizar.style.backgroundColor = '#28a745';
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

    // Clean up timer when leaving the page
    window.addEventListener('beforeunload', function () {
        if (statusTimer) {
            clearInterval(statusTimer);
        }
    });
});
