// Initialize AOS
AOS.init({
    duration: 1000,
    once: true
});

// Navbar scroll effect
window.addEventListener('scroll', function () {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.style.background = 'rgba(26, 26, 26, 0.98)';
    } else {
        navbar.style.background = 'rgba(26, 26, 26, 0.95)';
    }
});

// Smooth scrolling
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Chat functionality
let chatHistory = [];

function toggleChat() {
    const chatWindow = document.getElementById('chatWindow');
    const toggle = document.querySelector('.chat-toggle');

    if (chatWindow.style.display === 'none' || chatWindow.style.display === '') {
        chatWindow.style.display = 'flex';
        toggle.innerHTML = '<i class="fas fa-times"></i>';
    } else {
        chatWindow.style.display = 'none';
        toggle.innerHTML = '<i class="fas fa-comments"></i>';
    }
}

function handleChatEnter(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

async function sendMessage() {
    const input = document.getElementById('chatInput');
    const messageText = input.value.trim();

    if (messageText) {
        addMessage(messageText, 'user');
        input.value = '';
        input.disabled = true; // Deshabilitamos el input mientras la IA piensa

        // Añadimos un indicador de "escribiendo..."
        const messagesContainer = document.getElementById('chatMessages');
        const typingIndicator = document.createElement('div');
        typingIndicator.id = 'typing-indicator';
        typingIndicator.innerHTML = `
            <div class="message-bot mb-3">
                <div class="bg-light p-2 rounded">
                    <i class="fas fa-spinner fa-spin"></i> Sofía está escribiendo...
                </div>
            </div>`;
        messagesContainer.appendChild(typingIndicator);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;


        try {
            // Hacemos la llamada a nuestro backend
            const response = await fetch('php/chatbot_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                // Enviamos todo el historial para que la IA tenga contexto
                body: JSON.stringify({ messages: chatHistory }),
            });

            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor.');
            }

            const data = await response.json();
            const aiReply = data.reply;

            // Quitamos el indicador de "escribiendo..."
            document.getElementById('typing-indicator').remove();
            // Añadimos la respuesta real de la IA
            addMessage(aiReply, 'bot');

        } catch (error) {
            console.error('Error:', error);
            document.getElementById('typing-indicator').remove();
            addMessage('Lo siento, estoy teniendo problemas técnicos. Inténtalo más tarde.', 'bot');
        } finally {
            input.disabled = false; // Rehabilitamos el input
            input.focus();
        }
    }
}
function addMessage(message, sender) {
    const messagesContainer = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message-${sender} mb-3`;

    const now = new Date().toLocaleTimeString('es-CL', {
        hour: '2-digit',
        minute: '2-digit'
    });

    if (sender === 'user') {
        messageDiv.innerHTML = `
                    <div class="text-end">
                        <div class="bg-primary text-white p-2 rounded d-inline-block">
                            ${message}
                        </div>
                        <br><small class="text-muted">${now}</small>
                    </div>
                `;
    } else {
        messageDiv.innerHTML = `
                    <div class="bg-light p-2 rounded">
                        ${message}
                    </div>
                    <small class="text-muted">${now}</small>
                `;
    }

    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    chatHistory.push({ role: sender === 'user' ? 'user' : 'assistant', content: message });
}

function generateBotResponse(userMessage) {
    const message = userMessage.toLowerCase();

    // Simple keyword-based responses (in production, this would use OpenAI API)
    if (message.includes('horario') || message.includes('hora')) {
        return '🕐 Nuestros horarios son:<br>• Lunes a Viernes: 6:00 - 22:00<br>• Sábados: 8:00 - 18:00<br>• Domingos: Cerrado<br><br>¿Te interesa alguna clase en particular?';
    } else if (message.includes('precio') || message.includes('plan') || message.includes('costo')) {
        return '💰 Tenemos 3 planes:<br>• Básico: $25.000/mes<br>• Premium: $35.000/mes (¡Más popular!)<br>• VIP: $50.000/mes<br><br>Además, tu primera clase es ¡GRATIS! 🎉';
    } else if (message.includes('ubicación') || message.includes('dirección') || message.includes('donde')) {
        return '📍 Estamos ubicadas en:<br>Av. Providencia 1234, Providencia<br><br>¡Muy cerca del metro! ¿Necesitas indicaciones más específicas?';
    } else if (message.includes('agendar') || message.includes('reservar') || message.includes('clase')) {
        return '📅 ¡Perfecto! Para agendar tu clase gratuita necesito:<br>• Tu nombre<br>• Número de teléfono<br>• Clase de tu interés<br><br>¿Podrías compartir esos datos conmigo?';
    } else if (message.includes('hola') || message.includes('holi')) {
        return '¡Hola bella! 👋✨ Soy Sofía y estoy aquí para ayudarte con todo sobre FitGirl Studio. ¿Qué te gustaría saber? Puedo contarte sobre nuestras clases, horarios, planes o ayudarte a agendar tu clase gratuita 💪';
    } else {
        return '¡Genial pregunta! 😊 Te puedo ayudar con:<br>• Horarios de clases<br>• Planes y precios<br>• Ubicación del gym<br>• Agendar tu clase GRATIS<br><br>¿Sobre qué te gustaría saber más?';
    }
}

// Contact form submission
/*document.querySelector('form').addEventListener('submit', function (e) {
    e.preventDefault();

    // Get form data
    const formData = new FormData(this);

    // Show success message
    alert('¡Gracias! Hemos recibido tu solicitud. Te contactaremos pronto para confirmar tu clase gratuita. 💪✨');

    // Reset form
    this.reset();

    // In production, you would send this data to your PHP backend
    console.log('Form submitted:', Object.fromEntries(formData));
});*/

// Add some interactive animations
document.querySelectorAll('.class-card, .plan-card, .testimonial-card').forEach(card => {
    card.addEventListener('mouseenter', function () {
        this.style.transform = 'translateY(-10px) scale(1.02)';
    });

    card.addEventListener('mouseleave', function () {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

/*    // Loading animation for buttons
    document.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (this.type === 'submit' || this.innerHTML.includes('Agendar') || this.innerHTML.includes('Elegir')) {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            this.disabled = true;

            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 2000);
        }
    });
});*/

// Hero parallax effect
window.addEventListener('scroll', function () {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero');
    if (hero) {
        hero.style.transform = `translateY(${scrolled * 0.5}px)`;
    }
});

// Add floating animation to WhatsApp button
setInterval(function () {
    const whatsappBtn = document.querySelector('.whatsapp-btn');
    if (whatsappBtn) {
        whatsappBtn.style.animation = 'bounce 1s ease-in-out';
        setTimeout(() => {
            whatsappBtn.style.animation = '';
        }, 1000);
    }
}, 5000);

// CSS for bounce animation
const style = document.createElement('style');
style.textContent = `
    @keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
}
    40% {
    transform: translateY(-10px);
}
    60% {
    transform: translateY(-5px);
}
}
    `;
document.head.appendChild(style);

console.log('🎉 FitGirl Studio - Desarrollado con amor por [tu nombre]');
