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
              :class="{'bg-red-200 text-red-700': highlightNegatives && player.roundTotals[roundIndex] && player.roundTotals[roundIndex].score < 0}">
            <div class="flex justify-between px-2">
              <span class="w-20 text-center"
                    v-if="showCumulativeTotals(player)">{{ getPlayerCumulativeTotal(player, round) }}</span>
              <button type="button" @click="halfIt(player,round)"
                      class="px-2 bg-red-600 text-white text-2xl rounded-full" v-if="game === 'Halfit'">½
              </button>
              <img alt="Scotty!" class="rounded-full w-8 h-8 cursor-pointer" src="/assets/images/scotty.png"
                   @click="subtractRoundScore(player,round)" v-if="game === 'Scotty\'s Game'">

              <!-- Martyn's Game Logic -->
              <template v-if="game === 'Martyn\'s Game'">
                <!-- Show Martyn face button when face scores are hidden and score is 0 or positive -->
                <img alt="Martyn!" class="rounded-full w-8 h-8 cursor-pointer" src="/assets/images/martyn.png"
                     @click="subtractRandomPoints(player,round)"
                     v-if="!showMartynsMinuses && player.roundTotals[roundIndex].score >= 0 && !showTotals(player)">

                <!-- Show Martyn face button when face scores are visible and score is 0 or positive -->
                <img alt="Martyn!" class="rounded-full w-8 h-8 cursor-pointer" src="/assets/images/martyn.png"
                     @click="subtractRandomPoints(player,round)"
                     v-if="showMartynsMinuses && player.roundTotals[roundIndex].score >= 0 && !showTotals(player)">

                <!-- Show undo button in place of input when face scores are hidden and score is negative -->
                <button type="button" @click="undoMartynsScore(player,round)"
                        class="w-20 bg-orange-600 text-white text-2xl rounded text-center"
                        v-if="!showMartynsMinuses && player.roundTotals[roundIndex].score < 0"
                        title="Undo Martyn's face">⎌</button>
              </template>

              <input type="number" class="w-20 text-right" v-show="showScore(player,round)"
                     v-model.number="player.roundTotals[roundIndex].score"
                     :step="['Shanghai'].indexOf(game) > -1 ? round : 1"
                     :max="['Shanghai'].indexOf(game) > -1 ? round * 9 : ['Scotty\'s Game', 'Martyn\'s Game'].indexOf(game) > -1 ? 9 : null">
            </div>
            <p class="text-xs text-center px-5"><span>{{ player.name }}<br>Round: {{ round }}</span></p>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="game === 'Martyn\'s Game'">
      <button type="button" class="bg-slate-50 rounded-lg border-2 px-2 py-1 shadow-md"
              :class="{'border-green-800 text-green-800':showMartynsMinuses,'border-red-800 text-red-800': !showMartynsMinuses}"
              @click="showMartynsMinuses = !showMartynsMinuses">
        <span v-if="!showMartynsMinuses">Show</span><span v-else>Hide</span> "Martyn's face" scores
      </button>
    </div>

  </div>
</template>

<script>
import _ from 'lodash';

export default {
  props: {
    startPlayers: [Array, String],
    game: String
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
    undoMartynsScore(player, round) {
      // Reset the score back to 0 (unplayed)
      this.setPlayerRoundScore(player, round, 0);
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
                if (_this.game === "Martyn's Game" && _this.showMartynsMinuses === false && thisRoundScore < 0) {
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
        // Always show input fields for all rounds, just like Scotty's Game
        return true;
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
    console.log('=== CUMULATIVE SCORES COMPONENT MOUNTED ===');
    console.log('Component mounted with game:', this.game);
    console.log('Start players raw:', this.startPlayers);
    console.log('Start players type:', typeof this.startPlayers);
    console.log('Start players length:', this.startPlayers ? this.startPlayers.length : 'undefined');

    // Check if lodash is available
    console.log('Lodash available:', typeof _);

    try {
      if (this.game === "Shanghai") {
        console.log('Setting up Shanghai game...');
        _.range(1, 10).forEach(round => this.rounds.push(round));
        this.allowKillers = true;
        this.allowAddRounds = true;
      }
      else if (this.game === "Halfit") {
        console.log('Setting up Halfit game...');
        const roundsToAdd = _.concat(this.startRounds, _.take(_.shuffle(this.randomRounds), 3), this.finalRounds);
        console.log('Rounds to add:', roundsToAdd);
        roundsToAdd.forEach(round => this.rounds.push(round));
        this.allowAddRounds = true;
        this.allowEditRoundNames = true;
      }
      else if (["Scotty's Game", "Martyn's Game"].indexOf(this.game) > -1) {
        console.log('Setting up', this.game);
        _.range(1, 21).forEach(round => this.rounds.push(round));
        this.highlightNegatives = true;
        this.hideTotalsUntilEveryoneIsFinished = true;
      }
    } catch (error) {
      console.error('Error setting up rounds:', error);
    }

    console.log('Rounds created:', this.rounds);

    try {
      if (this.startPlayers && this.startPlayers.length > 0) {
        console.log('Processing', this.startPlayers.length, 'players...');

        // Handle both string JSON and already parsed array
        let playersData = this.startPlayers;
        if (typeof this.startPlayers === 'string') {
          playersData = JSON.parse(this.startPlayers);
        }

        playersData.forEach((starter, index) => {
          console.log(`Player ${index}:`, starter);
          // Handle Craft CMS entry objects - they have a 'title' property directly
          const playerName = starter.title || starter.name || `Player ${index + 1}`;
          console.log(`Player ${index} name:`, playerName);
          this.addPlayer(playerName);
        });
      } else {
        console.log('No start players provided or startPlayers is empty');
      }
    } catch (error) {
      console.error('Error processing players:', error);
      console.error('Error details:', error.message);
      console.error('Start players data:', this.startPlayers);
    }

    console.log('Players after initialization:', this.players);

    try {
      this.$emit('mounted', _.concat(this.startRounds, this.randomRounds, this.finalRounds));
      console.log('Mounted event emitted successfully');
    } catch (error) {
      console.error('Error emitting mounted event:', error);
    }

    console.log('=== COMPONENT INITIALIZATION COMPLETE ===');
  }
}
</script>

<style scoped>

</style>
