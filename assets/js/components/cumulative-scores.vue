<template>
  <div class="text-center">
    <button type="button" class="form-input" @click="addPlayer" v-if="rounds.length > 0">Add a player</button>
    <div v-if="game === 'Martyn\'s Game'">
      <label>
        <input type="checkbox" class="form-checkboxes" v-model="hideTotalsUntilEveryoneIsFinished">
        Hide totals until last score is entered
      </label><br>
      <label>
        <input type="checkbox" class="form-checkboxes" v-model="showRunningTotals">
        Show running totals
      </label>
    </div>

    <button type="button" class="form-input" @click="addRound" v-if="allowAddRounds">Add a round</button>
    <div class="inline" v-if="allowKillers">
      <button type="button" class="form-input" @click="toggleKillers('even')">Toggle Even killers</button>
      <button type="button" class="form-input" @click="toggleKillers('odd')">Toggle Odd killers</button>
    </div>
    <table class="table-auto rounded-lg my-5 mx-auto shadow-xl">
      <thead>
      <tr class="bg-slate-50 border">
        <th class="">Rounds</th>
        <th v-for="(player, playerIndex) in players" class="border px-2">
          <input v-model="player.name" type="text" class="w-24 text-center font-bold">
          <button type="button" @click="removePlayer(playerIndex)" tabindex="-1">x</button>
          <div
              :class="{'flex justify-between': showCumulativeTotals(player), 'text-right': !showCumulativeTotals(player)}">
            <span class="text-base mr-2" v-if="showCumulativeTotals(player)">Running<br>Total</span>
            <span class="text-base ml-2">Round<br>Score</span>
          </div>
        </th>
      </tr>
      </thead>
      <tbody>
      <draggable tag="tr" v-model="rounds" @change="rescore">
        <tr v-for="(round, roundIndex) in rounds" class="bg-white"
            :class="{'bg-red-200 text-red-700':((roundIndex % 2 !== 0 && evenKillers) ||(roundIndex % 2 === 0 && oddKillers) )}"
            :key="roundIndex">
          <td class="px-2 border py-2">
            <div class="flex justify-between">
              <input type="text" class="text-center text-2xl"
                     :class="{'w-24': game !== 'Halfit','w-56': game === 'Halfit'}" v-model="rounds[roundIndex]"
                     :readonly="! allowEditRoundNames">
              <button type="button" @click="removeRound(roundIndex)" tabindex="-1">x</button>
            </div>
          </td>

          <td v-for="(player, playerIndex) in players" class="border" :key="playerIndex"
              :class="{'bg-red-200 text-red-700': highlightNegatives && player.roundTotals[roundIndex].score < 0}">
            <div class="flex justify-between px-2">
              <span class="w-20 text-center"
                    v-if="showCumulativeTotals(player)">{{ getPlayerCumulativeTotal(player, round) }}</span>
              <button type="button" @click="halfIt(player,round)"
                      class="px-2 bg-red-600 text-white text-2xl rounded-full" v-if="game === 'Halfit'">½
              </button>
              <img alt="Scotty!" class="rounded-full w-8 h-8 cursor-pointer" src="/assets/images/scotty.png"
                   @click="subtractRoundScore(player,round)" v-if="game === 'Scotty\'s Game'">
              <img alt="Martyn!" class="rounded-full w-8 h-8 cursor-pointer" src="/assets/images/martyn.png"
                   @click="subtractRandomPoints(player,round)" v-if="game === 'Martyn\'s Game' && !showTotals(player)">
              <input type="number" class="w-20 text-right" v-show="showScore(player,round)"
                     v-model.number="player.roundTotals[roundIndex].score"
                     :step="['Shanghai'].indexOf(game) > -1 ? round : 1"
                     :max="['Shanghai'].indexOf(game) > -1 ? round * 9 : ['Scotty\'s Game', 'Martyn\'s Game'].indexOf(game) > -1 ? 9 : null">
            </div>
            <p class="text-xs text-center px-5"><span>{{ player.name }}<br>Round: {{ round }}</span></p>
          </td>
        </tr>
      </draggable>
      <tr class="bg-slate-50 text-3xl">
        <th class="px-4 py-2">Scores</th>
        <th v-for="(player, playerIndex) in players" :key="playerIndex">
          <span v-if="showTotals(player)">{{ player.name }}: {{ getPlayerTotal(player) }}</span>
        </th>
      </tr>
      </tbody>
    </table>

    <div v-if="game === 'Martyn\'s Game'">
      <button type="checkbox" class="bg-slate-50 rounded-lg border-2 px-2 py-1 shadow-md"
              :class="{'border-green-800 text-green-800':showMartynsMinuses,'border-red-800 text-red-800': !showMartynsMinuses}"
              @click="showMartynsMinuses = !showMartynsMinuses">
        <span v-if="!showMartynsMinuses">Show</span><span v-else>Hide</span> "Martyn's face" scores
      </button>
    </div>

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
      allowEditRoundNames: false,
      allowAddRounds: false,
      players: [],
      allowKillers: false,
      oddKillers: false,
      evenKillers: false,
      highlightNegatives: false,
      hideTotalsUntilEveryoneIsFinished: false,
      showRunningTotals: true,
      showMartynsMinuses: false
    }
  },
  computed: {},
  methods: {
    addRound() {
      let randomRound = _.sample(this.randomRounds);
      if (this.game === "Shanghai") {
        randomRound = this.rounds.length + 1;
        if (randomRound === 20) {
          // you can only add up to 20
          this.allowAddRounds = false;
        }
      }
      this.rounds.push(randomRound);
      this.players.forEach(player => player.roundTotals.push({round: randomRound, score: 0}));
    },
    removeRound(roundIndex) {
      this.players.forEach(player => player.roundTotals.splice(roundIndex, 1));
      this.rounds.splice(roundIndex, 1);
    },
    addPlayer(playerName) {
      this.allowEditRoundNames = false;
      playerName = typeof playerName === "object" ? 'Player' + (this.players.length + 1) : playerName;
      let roundTotals = _.map(this.rounds, function (round) {
        return {round: round, score: 0}
      });
      this.players.push(
          {name: playerName, roundTotals: roundTotals});
    },
    removePlayer(index) {
      this.players.splice(index, 1);
      if (this.players.length === 0) {
        if (this.game === "Halfit") {
          this.allowEditRoundNames = true;
        }
      }
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
      if (player.roundTotals.length === 0) {
        return;
      }
      if (this.game === "Martyn's Game" && !this.showMartynsMinuses) {
        return player.roundTotals.map(round => round.score).reduce(((previousValue, currentValue) => currentValue > 0 ? previousValue + currentValue : previousValue + 0), 0)
      }
      return player.roundTotals.map(round => round.score).reduce((previousValue, currentValue) => previousValue + currentValue)
    },
    getPlayerCumulativeTotal(player, round) {
      let playerTotal = 0;
      let found = false;
      let _this = this;
      this.rounds.forEach(function (roundName) {
            if (roundName === round) {
              found = true;
            }
            let thisRound = player.roundTotals.filter(total => total.round === roundName);
            if (thisRound.length > 0) {
              let thisRoundScore = thisRound[0].score;
              if (!found) {
                if (thisObj.game === "Martyn's Game" && thisObj.showMartynsMinuses === false && thisRoundScore < 0) {
                  playerTotal += 0; //don't show the minus score yet
                }
                else {
                  playerTotal += thisRoundScore;
                }
              }
            }
          }
      );

      return playerTotal;
    },
    rescore() {
      let thisObj = this;
      thisObj.players.forEach(function (player) {
        thisObj.rounds.forEach(function (roundName) {
          let thisRound = player.roundTotals.filter(total => total.round === roundName);
          if (thisRound.length > 0) {
            let thisRoundScore = thisRound[0].score;
            if (thisRoundScore < 0) {
              thisObj.halfIt(player, roundName);
            }
          }
        });
      });
    },
    toggleKillers(which) {
      if (which === 'odd') {
        this.oddKillers = !this.oddKillers;
      }
      else {
        this.evenKillers = !this.evenKillers;
      }
    },
    showScore(player, round) {
      if (['Martyn\'s Game'].indexOf(this.game) > -1) {
        let playerRound = _.find(player.roundTotals, {round: round});
        if (!this.showMartynsMinuses && playerRound.score < 0) {
          return false;
        }
        return ((playerRound.score > -1) || this.showTotals(player));
      }
      return true;
    },
    showCumulativeTotals(player) {
      if (['Martyn\'s Game'].indexOf(this.game) > -1) {
        return this.showRunningTotals;
      }
      return true;
    },
    showTotals(player) {
      if (['Martyn\'s Game'].indexOf(this.game) > -1) {
        let playersWithRoundsLeft = [];
        if (this.hideTotalsUntilEveryoneIsFinished) {
          playersWithRoundsLeft = this.players.filter(player =>
              player.roundTotals.filter(round =>
                  round.score === 0
              ).length > 0
          );
        }
        let roundsCompleted = player.roundTotals.filter(round => round.score !== 0);
        // probably don't need the first comparison here.
        return (player.roundTotals.length === roundsCompleted.length && playersWithRoundsLeft.length === 0);
      }
      // just show the totals if it's not handled above
      return true;
    },
  }
  ,
  mounted() {

    if (this.game === "Shanghai") {
      _.range(1, 10).forEach(round => this.rounds.push(round));
      this.allowKillers = true;
      this.allowAddRounds = true;
    }
    else if (this.game === "Halfit") {
      _.concat(this.startRounds, _.take(_.shuffle(this.randomRounds), 3), this.finalRounds).forEach(round => this.rounds.push(round));
      this.allowAddRounds = true;
      this.allowEditRoundNames = true;

    }
    else if (["Scotty's Game", "Martyn's Game"].indexOf(this.game) > -1) {
      _.range(1, 21).forEach(round => this.rounds.push(round));
      this.highlightNegatives = true;
      this.hideTotalsUntilEveryoneIsFinished = true;
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
