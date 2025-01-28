// Funzione per cambiare il tema
const toggleButton = document.getElementById('theme-toggle');
const body = document.body;

toggleButton.addEventListener('click', () => {
    // Cambia tra tema scuro e chiaro
    body.classList.toggle('light-theme');

    // Aggiorna l'icona del pulsante
    if (body.classList.contains('light-theme')) {
        toggleButton.textContent = '☀️'; // Icona per tema chiaro
    } else {
        toggleButton.textContent = '🌙'; // Icona per tema scuro
    }
});

// Controllo per applicare il tema salvato nel localStorage
window.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    if (savedTheme === 'light') {
        body.classList.add('light-theme');
        toggleButton.textContent = '☀️';
    }
});

// Salva il tema attuale nel localStorage
body.addEventListener('classchange', () => {
    const theme = body.classList.contains('light-theme') ? 'light' : 'dark';
    localStorage.setItem('theme', theme);
});


