<?php
// php/chatbot_handler.php

require_once 'config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$messages = $input['messages'] ?? [];

if (empty($messages)) {
    echo json_encode(['error' => 'No se recibió ningún mensaje.']);
    exit;
}

// --- Preparación de la Petición a Google Gemini ---

// Usaremos Gemini 1.5 Flash, es rápido y muy capaz.
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . GEMINI_API_KEY;
$apiKey = GEMINI_API_KEY;

// La personalidad que le damos a nuestro chatbot para Gemini
$systemInstruction = [
    'role' => 'system',
    'parts' => [
        ['text' => 'Eres Sofía, una asistente virtual experta, amigable y motivadora del gimnasio femenino "FitGirl Studio". Tu objetivo es responder preguntas, dar información sobre horarios, precios (Básico: $25.000, Premium: $35.000, VIP: $50.000), clases (Yoga, Pilates, CrossFit, Danza) y animar a las usuarias a agendar su clase de prueba gratuita. Sé siempre positiva y usa emojis de vez en cuando. La ubicación es Av. Providencia 1234. Mantén las respuestas concisas y amigables.']
    ]
];

// El formato de Gemini es ligeramente diferente. Tenemos que adaptar el historial.
$contents = [];
foreach ($messages as $msg) {
    // El rol 'assistant' de OpenAI se llama 'model' en Gemini
    $role = ($msg['role'] === 'assistant') ? 'model' : 'user';
    $contents[] = [
        'role' => $role,
        'parts' => [['text' => $msg['content']]]
    ];
}

$data = [
    'contents' => $contents,
    'systemInstruction' => $systemInstruction,
    'generationConfig' => [
        'temperature' => 0.8,
        'maxOutputTokens' => 250,
    ]
];

// --- Envío de la Petición con cURL ---
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// --- Procesamiento de la Respuesta ---
if ($httpcode >= 400) {
    echo json_encode(['reply' => '¡Ups! Parece que mi cerebro artificial (Gemini) está descansando. Por favor, intenta de nuevo en un momento.']);
} else {
    $result = json_decode($response, true);
    // La respuesta de Gemini está en una estructura diferente
    $reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'No sé qué decir a eso. ¿Puedes preguntarme otra cosa?';
    echo json_encode(['reply' => $reply]);
}
?>