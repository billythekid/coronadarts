<template>
  <div class="text-center">
    <button type="button" class="form-input" @click="addPlayer" v-if="rounds.length > 0">Add a player</button>
    <button type="button" class="form-input" @click="addRound" v-if="['Shanghai', 'Scotty\'s Game'].indexOf(game) === -1">Add a round</button>
    <button type="button" class="form-input" @click="toggleKillers('even')">Toggle Even killers</button>
    <button type="button" class="form-input" @click="toggleKillers('odd')">Toggle Odd killers</button>

    <table class="table-auto rounded-lg my-5 mx-auto shadow-xl">
      <thead>
      <tr class="bg-gray-100 border">
        <th class="">Rounds</th>
        <th v-for="(player, playerIndex) in players" class="border px-2">
          <input v-model="player.name" type="text" class="w-24 text-center font-bold">
          <button type="button" @click="removePlayer(playerIndex)" tabindex="-1">x</button>
          <div class="flex justify-between">
            <span class="text-base">Running<br>Total</span>
            <span class="text-base">Round<br>Score</span>
          </div>
        </th>
      </tr>
      </thead>
      <draggable tag="tbody" v-model="rounds" @change="rescore">
        <tr v-for="(round, roundIndex) in rounds" class="bg-white" :class="{'bg-red-200 text-red-700':((roundIndex % 2 !== 0 && evenKillers) ||(roundIndex % 2 === 0 && oddKillers) )}" :key="roundIndex">
          <td class="px-2 border py-2">
            <div class="flex justify-between">
              <input type="text" class="text-center text-2xl" :class="{'w-24': game !== 'Halfit','w-56': game === 'Halfit'}" v-model="rounds[roundIndex]" :readonly="players.length > 0 || ['Shanghai', 'Scotty\'s Game', 'Martyn\'s Game'].indexOf(game) > -1">
              <button type="button" @click="removeRound(roundIndex)" tabindex="-1">x</button>
            </div>
          </td>

          <td v-for="(player, playerIndex) in players" class="border" :key="playerIndex" :class="{'bg-red-200 text-red-700': highlightNegatives && player.roundTotals[roundIndex].score < 0}">
            <div class="flex justify-between px-2">
              <span class="w-20 text-center" v-if="showTotals(player)">{{ getPlayerCumulativeTotal(player,round) }}</span>
              <button type="button" @click="halfIt(player,round)" class="px-2 bg-red-600 text-white text-2xl rounded-full" v-if="game === 'Halfit'">½</button>
              <img class="rounded rounded-full w-8 h-8 cursor-pointer" src="/assets/images/scotty.png" @click="subtractRoundScore(player,round)" v-if="game === 'Scotty\'s Game'">
              <img class="rounded rounded-full w-8 h-8 cursor-pointer" src="/assets/images/martyn.png" @click="subtractRandomPoints(player,round)" v-if="game === 'Martyn\'s Game'">
              <input type="number" class="w-20 text-right" v-show="showScore(player,round)" v-model.number="player.roundTotals[roundIndex].score" :step="['Shanghai'].indexOf(game) > -1 ? round : 1" :max="['Shanghai'].indexOf(game) > -1 ? round * 9 : ['Scotty\'s Game', 'Martyn\'s Game'].indexOf(game) > -1 ? 9 : null">
            </div>
            <p class="text-xs text-center px-5"><span>{{ player.name }}<br>Round: {{ round }}</span></p>
          </td>
        </tr>
      </draggable>
      <tr class="bg-gray-100 text-3xl">
        <th class="px-4 py-2">Scores</th>
        <th v-for="(player, playerIndex) in players" :key="playerIndex">
          <span v-if="showTotals(player)">{{ player.name }}: {{ getPlayerTotal(player) }}</span>
        </th>
      </tr>
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
        startRounds: [
          'Any Score',
          '20s',
          'Doubles',
          '19s',
          'Trebles',
        ],
        randomRounds: [
          '18s',
          'Reds',
          'Greens',
          '13s',
          '14s',
          '9s',
          'Black white black',
          '3 different big blacks',
          '3 different small blacks',
          '3 different big whites',
          '3 different small whites',
          'Hit 61 (3 different scoring darts)',
          '3 different colours',
        ],
        finalRounds: ['25s', 'Bullseyes'],
        players: [],
        oddKillers: false,
        evenKillers: false,
        highlightNegatives: false,
      }
    },
    computed: {},
    methods: {
      addRound() {
        let randomRound = _.sample(this.randomRounds);
        if (this.game === "Shanghai") {
          randomRound = this.rounds.length + 1;
        }
        this.rounds.push(randomRound);
        this.players.forEach(player => player.roundTotals.push({round: randomRound, score: 0}));
      },
      removeRound(roundIndex) {
        this.rounds.splice(roundIndex, 1);
        this.players.forEach(player => player.roundTotals.splice(roundIndex, 1));
      },
      addPlayer(playerName) {
        playerName = typeof playerName === "object" ? 'Player' + (this.players.length + 1) : playerName;
        let roundTotals = _.map(this.rounds, function (round) {
          return {round: round, score: 0}
        });
        this.players.push(
          {name: playerName, roundTotals: roundTotals});
      },
      removePlayer(index) {
        this.players.splice(index, 1);
      },
      halfIt(player, round) {
        let score = -1 * Math.floor(this.getPlayerCumulativeTotal(player, round) / 2);
        this.setPlayerRoundScore(player, round, score);
        this.$forceUpdate();
      },
      subtractRoundScore(player, round) {
        let score = round * -1;
        this.setPlayerRoundScore(player, round, score);
      },
      subtractRandomPoints(player, round) {
        let max = 20;
        if (this.game === "Martyn's Game") {
          max = 5;
        }
        let score = _.random(1, max, false) * -1;
        this.setPlayerRoundScore(player, round, score);
      },
      setPlayerRoundScore(player, round, score) {
        let playerRound = _.find(player.roundTotals, {round: round});
        playerRound.score = score;
        this.$forceUpdate();
      },
      getPlayerTotal(player) {
        return player.roundTotals.map(round => round.score).reduce((previousValue, currentValue) => previousValue + currentValue)
      },
      getPlayerCumulativeTotal(player, round) {
        let playerTotal = 0;
        let found = false;
        this.rounds.forEach(function (roundName) {
            if (roundName === round) {
              found = true;
            }
            let thisRoundScore = player.roundTotals.filter(total => total.round === roundName)[0].score;
            if (!found) {
              playerTotal += thisRoundScore;
            }
          }
        );

        return playerTotal;
      },
      rescore() {
        let _this = this;
        _this.players.forEach(function (player) {
          _this.rounds.forEach(function (roundName) {
            let thisRoundScore = player.roundTotals.filter(total => total.round === roundName)[0].score;
            if (thisRoundScore < 0) {
              _this.halfIt(player, roundName);
            }
          });
        });
      },
      toggleKillers(which) {
        if (which === 'odd') {
          this.oddKillers = !this.oddKillers;
        } else {
          this.evenKillers = !this.evenKillers;
        }
      },
      showScore(player, round) {
        if (['Martyn\'s Game'].indexOf(this.game) > -1) {
          let playerRound = _.find(player.roundTotals, {round: round});
          return (playerRound.score > -1 || this.showTotals(player));
        }
        return true;
      },
      showTotals(player) {
        if (['Martyn\'s Game'].indexOf(this.game) > -1) {
          let roundsCompleted = player.roundTotals.filter(round => round.score !== 0);
          return (player.roundTotals.length === roundsCompleted.length);
        }
        return true;
      },
      isNextRound(player, round) {
        let playerRound = _.find(player.roundTotals, {round: round});
        return playerRound.score === 0;
      },
    },
    mounted() {

      if (this.game === "Shanghai") {
        _.range(1, 10).forEach(round => this.rounds.push(round));
      } else if (this.game === "Halfit") {
        _.concat(this.startRounds, _.take(_.shuffle(this.randomRounds), 3), this.finalRounds).forEach(round => this.rounds.push(round));
      } else if (["Scotty's Game", "Martyn's Game"].indexOf(this.game) > -1) {
        _.range(1, 21).forEach(round => this.rounds.push(round));
        this.highlightNegatives = true;
      }


      if (this.startPlayers.length > 0) {
        this.startPlayers.forEach(starter => this.addPlayer(starter.title))
      }
      this.$emit('mounted', _.concat(this.startRounds, this.randomRounds, this.finalRounds));
    }
  }
</script>

<style scoped>

</style>
