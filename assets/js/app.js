import LoseLives from "./components/lose-lives.vue";
import CompetitionsDropdown from "./components/competitions-dropdown.vue";
import CumulativeScores from "./components/cumulative-scores.vue";
import PlayerDropdown from "./components/player-dropdown.vue";

require('./bootstrap');

import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import VueSimpleAlert from "vue3-simple-alert";

// Create the main Vue app
const app = createApp({
    compilerOptions: {
        isCustomElement: (tag) => ['lose-lives', 'cumulative-scores'].includes(tag.toLowerCase()),
    },
    delimiters: ['${', '}'],
    data() {
        return {
            currentCompetition: null,
            players: [],
            competitions: [],
            halfitData: []
        }
    },
    mounted() {
        this.competitions = typeof competitions !== 'undefined' ? competitions : [];
    },
    methods: {
        competitionChange(competition) {
            this.currentCompetition = null;
            if (competition) {
                this.currentCompetition = competition;
            }
        },
        halfitMounted(data) {
            this.halfitData = data;
        }
    }
});

// Register components
app.component('lose-lives', LoseLives);
app.component('competitions-dropdown', CompetitionsDropdown);
app.component('cumulative-scores', CumulativeScores);
app.component('player-dropdown', PlayerDropdown);

// Use plugins
app.use(VueSimpleAlert);

// Auto-register all Vue components from the components directory
const files = require.context('./components', true, /\.vue$/i);
files.keys().forEach(key => {
    const componentName = key.split('/').pop().split('.')[0];
    const component = files(key).default;
    app.component(componentName, component);
});

// Create router (if needed)
const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/scorers',
            name: 'scorers',
            component: CumulativeScores
        }
    ],
});

app.use(router);

// Mount the app
window.app = app.mount('#app');
