const compactToggle = document.getElementById('compactToggle');
const motionToggle = document.getElementById('motionToggle');
const settingsSaved = document.getElementById('settingsSaved');

function updateSwitch(button, key) {
    const enabled = localStorage.getItem(key) === 'true';
    button.setAttribute('aria-checked', String(enabled));
}

function savePreference(button, key) {
    const nextValue = button.getAttribute('aria-checked') !== 'true';
    localStorage.setItem(key, String(nextValue));
    updateSwitch(button, key);
    document.body.classList.toggle(key === 'absenku-compact-mode' ? 'compact-ui' : 'reduce-motion', nextValue);
    settingsSaved.hidden = false;
}

updateSwitch(compactToggle, 'absenku-compact-mode');
updateSwitch(motionToggle, 'absenku-reduced-motion');
compactToggle.addEventListener('click', () => savePreference(compactToggle, 'absenku-compact-mode'));
motionToggle.addEventListener('click', () => savePreference(motionToggle, 'absenku-reduced-motion'));
