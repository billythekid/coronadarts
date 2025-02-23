<template>
  <div class="text-center">

    <div v-if="hasRounds" class="inline-block">
      <button type="button" class="form-input" @click="addRound">Add a round</button>
    </div>

    <button type="button" class="form-input" @click="addPlayer">Add a player</button>

    <div v-if="killerGame" class="inline-block">
      <span v-if="game=='25s and Bulls'">Target</span><span v-else>Starting Lives</span>:
      <input type="number" class="form-input" v-model="startLives" @change="startLivesChanged">
    </div>

    <div v-if="bustableGame" class="inline-block">
      <input type="checkbox" v-model="canBust" class="form-checkbox"> Too many busts?
    </div>

    <table class="table-auto rounded-lg my-5 mx-auto shadow-xl bg-white">
      <thead>
      <tr class="bg-slate-50 border">
        <th v-if="hasRounds">Rounds</th>
        <th v-for="(player, playerIndex) in players" class="border px-2"
            :class="{'text-slate-400 bg-slate-300': (decrementingLives && player.lives === 0)}">
          <input v-model="player.name" type="text" class="w-24 text-center font-bold"
                 :class="{'text-slate-400 bg-slate-300': (decrementingLives && player.lives === 0)}">
          <button @click="removePlayer(playerIndex)" tabindex="-1">︎✗</button>
        </th>
      </tr>
      </thead>
      <tbody>
      <draggable tag="tr" v-model="rounds">
        <tr v-if="rounds.length > 0" v-for="(round, roundIndex) in rounds" class="bg-white">
          <th class="border">
            <div class="flex justify-around text-2xl">
              <button @click="removeRound(round)">︎✗</button>
              <input type="text" v-model.number="rounds[roundIndex]" class="w-10">
            </div>
          </th>
          <td v-for="(player, playerIndex) in players" class="border px-2 bg-white">
            <div class="p-2" v-if="! player.hitsOn50to60.includes(round) && ! player.missesOn50to60.includes(round)">
              <button @click="hit50to60(player, round)"
                      class="text-slate-500 border-slate-500 border-8 bg-white-500 rounded-full w-12 h-12">✓
              </button>
              <button @click="miss50to60(player, round)"
                      class="text-slate-500 border-slate-500 border-8 bg-white-500 rounded-full w-12 h-12">︎✗
              </button>
            </div>
            <div v-else-if="player.hitsOn50to60.includes(round)" class="flex justify-around p-2">
              <p class="text-green-500 border-green-500 border-8 bg-white-500 rounded-full w-12 h-12">✓</p>
              <button @click="removeHitOn50to60(player,round)" class="text-3xl">⎌</button>
            </div>
            <div v-else class="flex justify-around p-2">
              <p class="text-red-500 border-red-500 border-8 bg-white-500 rounded-full w-12 h-12 table-cell align-middle">
                ︎✗</p>
              <button @click="removeMissOn50to60(player,round)" class="text-3xl">⎌</button>
            </div>
          </td>
        </tr>
      </draggable>

      <tr v-if="game=='25s and Bulls'">
        <td v-for="(player, playerIndex) in players" class="border px-2 bg-white">
          <div class="flex justify-between p-5">
            <button @click="hit25(player)"
                    class="border-green-500 border-8 bg-white-500 text-green-500 rounded-full w-12 h-12">25
            </button>
            <div>&nbsp;</div>
            <button @click="hitBull(player)" class="border-black border-2 bg-red-500 text-white rounded-full w-16 h-16">
              BULL
            </button>
          </div>
          <p class="text-2xl">{{ player.lives }}</p>
        </td>
      </tr>

      <tr v-if="game=='27s'">
        <td v-for="(player, playerIndex) in players" class="px-2 bg-white"
            :class="{'text-slate-400 bg-slate-300': (decrementingLives && player.lives === 0)}">
          <div class="flex justify-center p-5" v-if="player.lives > 0">
            <button @click="missed27s(player)"
                    class="border-black border-2 bg-red-500 text-white  rounded-full w-16 h-16">︎✗
            </button>
          </div>
          <div class="flex justify-center p-5" v-else>
            <button class="border-black border-2 bg-slate-500 text-white rounded-full w-16 h-16">︎✗</button>
          </div>
          <p class="text-2xl">{{ player.lives }}</p>
        </td>
      </tr>

      <tr v-if="showGameTotals">
        <th v-if="game !== '27s' && game !== '25s and Bulls'">
          <div v-if="hasRounds" class="inline-block">
            <button type="button" class="form-input" @click="addRound">Add a round</button>
          </div>
          Scores:
        </th>
        <th v-for="player in players" :class="{'text-slate-600 bg-slate-300': decrementingLives && player.lives === 0}">
          {{ player.name }}: {{ player.lives }}
        </th>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import draggable from 'vuedraggable';

export default {
  props: {
    startPlayers: Array,
    game: String || null
  },
  components: {
    draggable
  },
  data() {
    return {
      rounds: [],
      killerGame: false,
      bustableGame: false,
      showGameTotals: false,
      decrementingLives: false,
      hasRounds: false,
      startLives: 10,
      players: [],
      knockedOutPlayers: [],
      canBust: true,
      oddKillers: false,
      evenKillers: false,
    }
  },
  methods: {
    addRound() {
      let lastRound = _.last(this.rounds);
      if (typeof lastRound === 'number')
        this.rounds.push(lastRound + 1);
      else this.rounds.push('New Round');
    },
    removeRound(round) {
      this.rounds.splice(this.rounds.indexOf(round), 1);
    },
    addPlayer(playerName) {
      playerName = typeof playerName === "object" ? 'Player' + (this.players.length + 1) : playerName;
      this.players.push(
          {
            name: playerName,
            lives: this.decrementingLives ? this.startLives : 0,
            hitsOn50to60: [],
            missesOn50to60: []
          })
    },
    removePlayer(index) {
      this.players.splice(index, 1);
    },
    startLivesChanged() {
      if (this.game === '27s') {
        this.players.forEach(player => player.lives = this.startLives);
      }
    },
    playerIsBust: function (player, howMany) {
      if (this.canBust && player.lives + howMany > this.startLives) {
        this.$fire({
          title: "BUST!",
          text: player.name + " is bust! Haha!",
          type: "error",
          timer: 2000,
          showConfirmButton: false,
        });
        return true;
      }
      return false;
    },
    check25BullWin(player) {
      if (player.lives >= this.startLives) {
        this.$fire({
          title: "WINNER!",
          text: player.name + " is the winner!",
          type: "success"
        });
      }
    },
    hit25(player) {
      if (!this.playerIsBust(player, 1)) {
        player.lives++;
        this.check25BullWin(player)
      }
    },
    hitBull(player) {
      if (!this.playerIsBust(player, 2)) {
        player.lives += 2;
        this.check25BullWin(player)
      }
    },
    hit50to60(player, round) {
      player.lives++;
      player.hitsOn50to60.push(round);
      this.check50to60Win();
    },
    miss50to60(player, round) {
      player.missesOn50to60.push(round);
      this.check50to60Win();
    },
    removeHitOn50to60(player, round) {
      player.lives--;
      player.hitsOn50to60.splice(player.hitsOn50to60.indexOf(round), 1);
    },
    removeMissOn50to60(player, round) {
      player.missesOn50to60.splice(player.missesOn50to60.indexOf(round), 1);
    },
    check50to60Win() {
      let playersPassed = [];
      let roundsToPass = _.clone(this.rounds).sort();
      this.players.forEach(function (player) {
        let roundsPassed = _.concat(player.hitsOn50to60, player.missesOn50to60).sort();
        if (_.difference(roundsToPass, roundsPassed).length === 0) {
          playersPassed.push(player);
        }
      });

      if (playersPassed.length === this.players.length) {
        // find winner(s)
        let allPlayers = _.clone(this.players).sort((a, b) => b.lives - a.lives);
        let topScore = _.first(allPlayers).lives;
        let winners = this.players.filter(player => player.lives === topScore);

        if (winners.length > 1) {
          this.$fire({
            title: "DRAW!",
            html: _.join(winners.map(winner => winner.name), ', ') + " have drawn!<br>Adding another round. Game on!",
            type: "info"
          });
          // setup additional rounds
          this.addRound();
        }
        else {
          let winner = _.first(winners);
          this.$fire({
            title: "WINNER!",
            text: winner.name + " is the winner with " + winner.lives + " points!",
            type: "success"
          });
        }
      }
    },
    missed27s(player) {
      player.lives--;
      this.$fire({
        title: "MISS!",
        text: player.name + " missed it! Haha!",
        type: "error",
        timer: 2000,
        showConfirmButton: false,
      }).then(result => this.check27sWin());
    },
    check27sWin() {
      let playersStillIn = this.players.filter(player => player.lives > 0);
      if (playersStillIn.length === 1) {
        this.$fire({
          title: "WINNER!",
          text: _.first(playersStillIn).name + " is the winner!",
          type: "success"
        });
      }
    },
    toggleKillers(which) {
      if (which === 'odd') {
        this.oddKillers = !this.oddKillers;
      }
      else {
        this.evenKillers = !this.evenKillers;
      }
    }
  },
  mounted() {
    if (this.game === "25s and Bulls") {
      this.bustableGame = true;
      this.killerGame = true;
      this.showGameTotals = true;
      this.startLives = 10;
      this.decrementingLives = false;
    }
    else if (this.game === "50 to 60") {
      this.hasRounds = true;
      this.showGameTotals = true;
      this.decrementingLives = false;
      _.range(50, 61).forEach(round => this.rounds.push(round));
    }
    else if (this.game === "27s") {
      this.killerGame = true;
      this.startLives = 5;
      this.showGameTotals = true;
      this.decrementingLives = true;
    }

    if (this.startPlayers.length > 0) {
      this.startPlayers.forEach(starter => this.addPlayer(starter.title));
    }
    if (this.decrementingLives) {
      this.players.forEach(player => player.lives = this.startLives);
    }
  }
}
</script>

<style scoped>

</style>
