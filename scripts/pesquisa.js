document.addEventListener('DOMContentLoaded', function () {
    // Check if user is logged in
    const userData = localStorage.getItem('userData');
    if (!userData) {
        window.location.href = 'tela_login.html';
        return;
    }

    const user = JSON.parse(userData);
    const pesquisaForm = document.getElementById('pesquisaForm');
    const resultado = document.getElementById('resultadoPesquisa');

    pesquisaForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const origem = document.getElementById('origem').value.trim();
        const destino = document.getElementById('destino').value.trim();

        if (!origem || !destino) {
            alert('Por favor, preencha origem e destino.');
            return;
        }

        try {
            // Create freight request
            const now = new Date();
            const data = now.toISOString().split('T')[0]; // YYYY-MM-DD
            const hora = now.toTimeString().split(' ')[0]; // HH:MM:SS

            const response = await fetch('/api/frete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_cliente: user.id_usu,
                    data: data,
                    hora: hora,
                    end_origem: origem,
                    end_destino: destino
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                resultado.style.display = 'block';
                resultado.innerHTML = `
                    <div style="color: green; margin-bottom: 15px;">
                        <strong>✅ Frete criado com sucesso!</strong><br>
                        ID do Frete: ${result.data.id_frete}<br>
                        Status: Pendente
                    </div>
                    <p>Agora vamos buscar motoristas disponíveis...</p>
                `;

                // Now fetch available drivers
                await loadAvailableDrivers(result.data.id_frete);

            } else {
                resultado.style.display = 'block';
                resultado.innerHTML = `<div style="color: red;">❌ Erro: ${result.error}</div>`;
            }
        } catch (error) {
            console.error('Error creating freight:', error);
            resultado.style.display = 'block';
            resultado.innerHTML = '<div style="color: red;">❌ Erro de conexão. Tente novamente.</div>';
        }
    });

    async function loadAvailableDrivers(freteId) {
        try {
            const response = await fetch('/api/login_usuarios/entregador');
            const result = await response.json();

            if (response.ok && result.success) {
                const drivers = result.data;

                if (drivers.length === 0) {
                    resultado.innerHTML += '<p>Nenhum motorista disponível no momento.</p>';
                    return;
                }

                let driversHtml = '<h4>Motoristas Disponíveis:</h4>';
                drivers.forEach(driver => {
                    driversHtml += `
                        <div style="border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px;">
                            <strong>${driver.nomecompleto}</strong><br>
                            Email: ${driver.email}<br>
                            Telefone: ${driver.telefone}<br>
                            <button onclick="selectDriver(${driver.id_usu}, ${freteId})" style="margin-top: 5px;">
                                Escolher este Motorista
                            </button>
                        </div>
                    `;
                });

                resultado.innerHTML += driversHtml;
            }
        } catch (error) {
            console.error('Error loading drivers:', error);
            resultado.innerHTML += '<p style="color: red;">Erro ao carregar motoristas.</p>';
        }
    }

    // Make selectDriver globally available
    window.selectDriver = async function (driverId, freteId) {
        try {
            console.log(`Selecting driver ${driverId} for freight ${freteId}`);

            const response = await fetch(`/api/frete/${freteId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_fretista: driverId,
                    status: 'aceito'
                })
            });

            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response received:', text);
                alert('Erro: Resposta inválida do servidor. Verifique os logs.');
                return;
            }

            const result = await response.json();
            console.log('Response data:', result);

            if (response.ok && result.success) {
                alert('Motorista selecionado com sucesso!');
                // Store current freight ID for the freight page
                localStorage.setItem('currentFreteId', freteId);
                window.location.href = 'tela_frete.html';
            } else {
                alert('Erro ao selecionar motorista: ' + (result.error || 'Erro desconhecido'));
            }
        } catch (error) {
            console.error('Error selecting driver:', error);
            alert('Erro de conexão ao selecionar motorista: ' + error.message);
        }
    };
});
