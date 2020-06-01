<template>
  <div class="text-center">
    <button type="button" class="form-input" @click="addPlayer">Add a player</button>
    <button type="button" class="form-input" @click="addRound">Add a round</button>

    <table class="table-auto rounded-lg my-5 mx-auto shadow-xl">
      <tr class="bg-gray-100 border">
        <th class="">Rounds</th>
        <th v-for="(player, playerIndex) in players" class="border px-2">
          <input :value="player.name" type="text" class="w-24 text-center font-bold">
          <button type="button" @click="removePlayer(playerIndex)">x</button>
          <div class="flex justify-between">
            <span class="text-base">Running<br>Total</span>
            <span class="text-base">Round<br>Score</span>
          </div>
        </th>
      </tr>
      <draggable tag="tbody" v-model="rounds" @change="rescore">
        <tr v-for="(round, roundIndex) in rounds" class="bg-white" :key="roundIndex">
          <td class="px-2 border py-2">
            <div class="flex justify-between">
              <input type="text" class="w-56 text-center" :value="round">
              <button type="button" @click="removeRound(round)">x</button>
            </div>
          </td>

          <td v-for="(player, playerIndex) in players" class="border" :key="playerIndex">
            <div class="flex justify-between">
              <span class="w-20 text-center">{{ getPlayerCumulativeTotal(player,round) }}</span>
              <button type="button" @click="halfIt(player,round)" class="px-2 bg-red-600 text-white text-2xl rounded-full">½</button>
              <input type="number" class="w-20 text-right" v-model.number="player.roundTotals.filter(total => total.round == round)[0].score">
            </div>
          </td>
        </tr>
      </draggable>
      <tr class="bg-gray-100 text-3xl">
        <th class="px-4 py-2">Scores</th>
        <th v-for="(player, playerIndex) in players">
          {{ player.name }}: {{ getPlayerTotal(player) }}
        </th>
      </tr>
    </table>
  </div>
</template>

<script>
  import draggable from 'vuedraggable';

  export default {
    props: {
      startPlayers: Array
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
        players: []
      }
    },
    methods: {
      addRound() {
        this.rounds.push('New Round');
      },
      removeRound(index) {
        this.rounds.splice(index, 1);
      },
      addPlayer(playerName) {
        playerName = typeof playerName === "object" ? 'Player' + (this.players.length + 1) : playerName;
        let roundTotals = _.map(this.rounds, function (round) {
          return {round: round, score: 0}
        });
        this.players.push(
          {
            name: playerName,
            roundTotals: roundTotals,
          },
        );
      },
      removePlayer(index) {
        this.players.splice(index, 1);
      },
      halfIt(player, round) {
        let score = -1 * Math.floor(this.getPlayerCumulativeTotal(player, round) / 2);
        this.setPlayerRoundScore(player, round, score);
        this.$forceUpdate();
      },
      setPlayerRoundScore(player, round, score) {
        let playerRound = _.find(player.roundTotals, {round: round});
        playerRound.score = score;
        this.$forceUpdate();
      },
      getPlayerTotal(player) {
        return player.roundTotals.map(round=>round.score).reduce((previousValue, currentValue )=> previousValue + currentValue)
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
        _this.players.forEach(function(player){
          _this.rounds.forEach(function (roundName) {
            let thisRoundScore = player.roundTotals.filter(total => total.round === roundName)[0].score;
            if (thisRoundScore < 0)
            {
              _this.halfIt(player,roundName);
            }
          });
        });
      }
    },
    mounted() {

      _.concat(this.startRounds, _.take(_.shuffle(this.randomRounds), 5), this.finalRounds).forEach(round => this.rounds.push(round));

      if (this.startPlayers.length > 0) {
        this.startPlayers.forEach(starter => this.addPlayer(starter))
      }
      this.$emit('mounted', _.concat(this.startRounds, this.randomRounds, this.finalRounds));
    }
  }
</script>

<style scoped>

</style>
