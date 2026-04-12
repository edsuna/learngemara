import Alpine from 'alpinejs';
import Cookies from 'js-cookie';

window.Alpine = Alpine;
window.Cookies = Cookies;

window.changeLanguage = () => {
    window.dispatchEvent(new CustomEvent('togglelanguage', { detail: 'toggle' }));
}

// Alpine.start() is called in app.js after all components are registered
