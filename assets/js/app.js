require('./bootstrap');


import VueRouter from 'vue-router';

Vue.use(VueRouter);

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
  },
  mounted() {
    this.competitions = competitions ?? [];
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
    }
  },

});
