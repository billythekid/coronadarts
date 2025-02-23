import LoseLives from "./components/lose-lives.vue";

require('./bootstrap');

import {createApp} from 'vue';

let Vue = createApp({});

import {createRouter, createWebHistory} from 'vue-router'

import VueSimpleAlert from "vue3-simple-alert";
import CompetitionsDropdown from "./components/competitions-dropdown.vue";
import CumulativeScores from "./components/cumulative-scores.vue";
import PlayerDropdown from "./components/player-dropdown.vue";

Vue.use(VueSimpleAlert);

const files = require.context('./', true, /\.vue$/i)
files.keys().forEach(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/scorers', components:[CompetitionsDropdown, CumulativeScores, LoseLives, PlayerDropdown] }
    ],
});

window.app = createApp({
    config: {
        compilerOptions: {
            isCustomElement: (tag) => ['lose-lives', 'cumulative-scores'].includes(tag.toLowerCase()),
        }
    },
    delimiters: ['${', '}'],
    el: '#app',
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
    watch: {},
    methods: {
        competitionChange(competition) {
            this.currentCompetition = null;
            if (competition) {
                this.currentCompetition = competition;
            }
        },
        player1Change(player) {
            this.playerChange(player, 0);
        },
        player2Change(player) {
            this.playerChange(player, 1)
        },
        playerChange(player, n) {
            this.players[n] = player;
        },
        halfitMounted(data) {
            this.halfitData = data;
        }
    },

});
app.use(router);
app.mount('#app');