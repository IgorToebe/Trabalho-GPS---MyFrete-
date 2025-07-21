document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatMessages = document.getElementById('chatMessages');

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const msg = chatInput.value.trim();
        if (msg) {
            addMessage(msg, 'user');
            chatInput.value = '';
            // Simulação de resposta automática
            setTimeout(() => {
                addMessage('Mensagem recebida pelo motorista!', '');
            }, 800);
        }
    });

    function addMessage(text, type) {
        const div = document.createElement('div');
        div.className = 'chat-message' + (type ? ' ' + type : '');
        div.textContent = text;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});
