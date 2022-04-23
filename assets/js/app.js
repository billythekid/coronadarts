require('./bootstrap');

import Vue from 'vue'
import VueRouter from 'vue-router';
Vue.use(VueRouter);

import VueSimpleAlert from "vue-simple-alert";
Vue.use(VueSimpleAlert);

const files = require.context('./', true, /\.vue$/i)
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

const router = new VueRouter({
  mode: 'history',
  base: '/',
  fallback: true,
  routes: [],
});

window.app = new Vue({
  router,
  delimiters: ['${', '}'],
  el: '#app',
  data: {
    currentCompetition: null,
    players: [],
    competitions: [],
    halfitData: []
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
