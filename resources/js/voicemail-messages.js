document.addEventListener('DOMContentLoaded', function() {
    initVoicemailPlayer();
    
    document.addEventListener('livewire:load', function() {
        Livewire.hook('message.processed', (message, component) => {
            initVoicemailPlayer();
        });
    });
});

function initVoicemailPlayer() {
    const playButtons = document.querySelectorAll('.btn-play-voicemail');
    
    playButtons.forEach(button => {
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        newButton.addEventListener('click', function() {
            const uuid = this.getAttribute('data-uuid');
            togglePlayVoicemail(uuid, this);
        });
    });
    
    const transcriptionButtons = document.querySelectorAll('.btn-toggle-transcription');
    transcriptionButtons.forEach(button => {
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        newButton.addEventListener('click', function() {
            const uuid = this.getAttribute('data-uuid');
            toggleTranscription(uuid);
        });
    });
}

function togglePlayVoicemail(uuid, button) {
    const audio = document.querySelector(`audio.voicemail-audio[data-uuid="${uuid}"]`);
    const progressBar = document.querySelector(`.progress-bar[data-uuid="${uuid}"]`);
    const icon = button.querySelector('i');
    
    if (!audio) {
        console.error('Audio element not found for UUID:', uuid);
        return;
    }
    
    document.querySelectorAll('audio.voicemail-audio').forEach(otherAudio => {
        if (otherAudio !== audio && !otherAudio.paused) {
            otherAudio.pause();
            otherAudio.currentTime = 0;

            const otherUuid = otherAudio.getAttribute('data-uuid');
            const otherButton = document.querySelector(`.btn-play-voicemail[data-uuid="${otherUuid}"]`);
            const otherProgressBar = document.querySelector(`.progress-bar[data-uuid="${otherUuid}"]`);
            
            if (otherButton) {
                otherButton.querySelector('i').className = 'fas fa-play';
            }
            if (otherProgressBar) {
                otherProgressBar.style.display = 'none';
                otherProgressBar.style.width = '0%';
            }
        }
    });
    
    if (audio.paused) {
        audio.play().then(() => {
            icon.className = 'fas fa-pause';
            if (progressBar) {
                progressBar.style.display = 'block';
            }
            
            markAsRead(uuid);
        }).catch(error => {
            console.error('Error playing audio:', error);
        });
    } else {
        audio.pause();
        icon.className = 'fas fa-play';
    }
    
    audio.ontimeupdate = function() {
        if (progressBar && audio.duration) {
            const progress = (audio.currentTime / audio.duration) * 100;
            progressBar.style.width = progress + '%';
        }
    };
    
    audio.onended = function() {
        icon.className = 'fas fa-play';
        if (progressBar) {
            progressBar.style.width = '0%';
            progressBar.style.display = 'none';
        }
        audio.currentTime = 0;
    };
    
    audio.onerror = function() {
        console.error('Error loading audio for UUID:', uuid);
        icon.className = 'fas fa-play';
        if (progressBar) {
            progressBar.style.display = 'none';
        }
    };
}

function toggleTranscription(uuid) {
    const transcriptionRow = document.getElementById('transcription_' + uuid);
    if (transcriptionRow) {
        transcriptionRow.style.display = transcriptionRow.style.display === 'none' ? 'table-row' : 'none';
    }
}

function markAsRead(uuid) {
    const audio = document.querySelector(`audio.voicemail-audio[data-uuid="${uuid}"]`);
    if (audio) {
        const row = audio.closest('tr');
        if (row) {
            row.querySelectorAll('span').forEach(span => {
                span.style.fontWeight = 'normal';
            });
        }
    } else {
        console.error('Elemento de audio no encontrado para markAsRead');
        return; 
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const voicemailUuid = audio.getAttribute('data-voicemail-uuid');
    
    if (!voicemailUuid) {
        console.error('voicemailUuid no encontrado en el elemento de audio');
        return;
    }

    const url = `/voicemails/${voicemailUuid}/messages/${uuid}/mark-read`;

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({}) 
    })
    .then(response => {
        if (!response.ok) {
            console.error('Error al marcar el mensaje como leído en el servidor');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('Mensaje marcado como leído en la base de datos.');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
    });
}
    
window.markAsRead = markAsRead;