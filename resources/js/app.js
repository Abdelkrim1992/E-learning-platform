import './bootstrap';
import {createApp} from 'vue';
import App from './components/App.vue';
import Router from './routes/Router.js';
import 'froala-editor/css/froala_style.min.css';
import 'froala-editor/css/froala_editor.pkgd.min.css';
import 'froala-editor/js/plugins.pkgd.min.js';
import VueFroala from 'vue-froala-wysiwyg';
import { createPinia } from 'pinia';
import './firebase';

const app = createApp(App);

app.use(Router)
   .use(createPinia())
   .use(VueFroala)
   .mount('#app');
