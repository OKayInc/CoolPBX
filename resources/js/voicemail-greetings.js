document.addEventListener('DOMContentLoaded', function() {
    initGreetingPlayer();
    initSetActiveGreeting();
    
    document.addEventListener('livewire:load', function() {
        Livewire.hook('message.processed', (message, component) => {
            initGreetingPlayer();
            initSetActiveGreeting();
        });
    });
});

function initGreetingPlayer() {
    const playButtons = document.querySelectorAll('.btn-play-greeting');
    
    playButtons.forEach(button => {
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        newButton.addEventListener('click', function() {
            const uuid = this.getAttribute('data-uuid');
            togglePlayGreeting(uuid, this);
        });
    });
}

function togglePlayGreeting(uuid, button) {
    const audio = document.querySelector(`audio.greeting-audio[data-uuid="${uuid}"]`);
    const progressBar = document.querySelector(`.progress-bar[data-uuid="${uuid}"]`);
    const icon = button.querySelector('i');
    
    if (!audio) {
        console.error('Audio element not found for UUID:', uuid);
        return;
    }
    
    document.querySelectorAll('audio.greeting-audio').forEach(otherAudio => {
        if (otherAudio !== audio && !otherAudio.paused) {
            otherAudio.pause();
            otherAudio.currentTime = 0;

            const otherUuid = otherAudio.getAttribute('data-uuid');
            const otherButton = document.querySelector(`.btn-play-greeting[data-uuid="${otherUuid}"]`);
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
        }).catch(error => {
            console.error('Error playing audio:', error);
            alert('Error playing audio. Please try again.');
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
        alert('Error loading audio file.');
    };
}

function initSetActiveGreeting() {
    const radioButtons = document.querySelectorAll('.set-active-greeting');

    radioButtons.forEach(radio => {
        const newRadio = radio.cloneNode(true);
        radio.parentNode.replaceChild(newRadio, radio);

        newRadio.addEventListener('change', function() {
            if (this.checked) {
                const greetingUuid = this.getAttribute('data-greeting-uuid');
                const voicemailId = this.getAttribute('data-voicemail-id');
                setActiveGreeting(voicemailId, greetingUuid, this);
            }
        });
    });
}



function setActiveGreeting(voicemailId, greetingUuid, radioElement) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const url = `/voicemails/${voicemailId}/greetings/${greetingUuid}/set-active`;

    const originalDisabled = radioElement.disabled;
    radioElement.disabled = true;

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
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Greeting set as active successfully', 'success');
            
            if (window.Livewire) {
                window.Livewire.emit('refresh');
            }
        } else {
            throw new Error(data.message || 'Failed to set greeting as active');
        }
    })
    .catch(error => {
        console.error('Error setting active greeting:', error);
        showNotification('Error setting active greeting: ' + error.message, 'error');
        
        radioElement.checked = false;
    })
    .finally(() => {
        radioElement.disabled = originalDisabled;
    });
}


function showNotification(message, type = 'info') {
    if (typeof toastr !== 'undefined') {
        toastr[type](message);
        return;
    }
    
    const alertClass = type === 'success' ? 'alert-success' : 
                       type === 'error' ? 'alert-danger' : 
                       'alert-info';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = alertHtml;
    document.body.appendChild(tempDiv.firstElementChild);

    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 3000);
}

window.togglePlayGreeting = togglePlayGreeting;
window.setActiveGreeting = setActiveGreeting;