import axios from 'axios';
window.axios = axios;

// Axios schickt bei jedem Request den CSRF-Token mit – das braucht Laravel für Formulare
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
