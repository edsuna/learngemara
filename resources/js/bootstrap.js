import Cookies from 'js-cookie';

window.Cookies = Cookies;

window.changeLanguage = () => {
    window.dispatchEvent(new CustomEvent('togglelanguage', { detail: 'toggle' }));
}
