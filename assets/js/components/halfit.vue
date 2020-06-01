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
      <tr v-for="(round, roundIndex) in rounds" class="bg-white">
        <td class="px-2 border py-2">
          <div class="flex justify-between">
            <input type="text" class="w-56 text-center" :value="round">
            <button type="button" @click="removeRound(roundIndex)">x</button>
          </div>
        </td>

        <td v-for="(player, playerIndex) in players" class="border">
          <div class="flex justify-between">
            <span class="w-20 text-center">{{ getPlayerCumulativeTotal(player,roundIndex) }}</span>
            <button type="button" @click="halfIt(player,roundIndex)" class="px-2 bg-red-600 text-white text-2xl rounded-full">½</button>
            <input type="number" class="w-20 text-right" v-model.number="player.roundTotals[roundIndex]">
          </div>
        </td>
      </tr>
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
  export default {
    props: {
      startPlayers: Array
    },
    data() {
      return {
        rounds: [],
        startRounds:[
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
        this.players.push(
          {
            name: playerName,
            roundTotals: this.rounds.map(round => 0),
          },
        );
      },
      removePlayer(index) {
        this.players.splice(index, 1);
      },
      halfIt(player, roundIndex) {
        player.roundTotals[roundIndex] = -1 * Math.floor(this.getPlayerCumulativeTotal(player, roundIndex) / 2);
        this.$forceUpdate();
      },
      getPlayerTotal(player) {
        return player.roundTotals.reduce((accumulator, currentValue) =>  accumulator + (currentValue !== '' ? currentValue : 0),0 )
      },
      getPlayerCumulativeTotal(player, roundIndex) {
        let playerTotal = 0;
        for (let i = 0; i < roundIndex; i++) {
          playerTotal = player.roundTotals[i] !== '' ? playerTotal + player.roundTotals[i] : 0;
        }
        return playerTotal;
      }
    },
    mounted() {

      _.concat(this.startRounds, _.take(_.shuffle(this.randomRounds), 5), this.finalRounds).forEach(round => this.rounds.push(round));

      if (this.startPlayers.length > 0) {
        this.startPlayers.forEach(starter => this.addPlayer(starter))
      }
      this.$emit('mounted', _.concat(this.startRounds,this.randomRounds,this.finalRounds));
    }
  }
</script>

<style scoped>

</style>
