<template>
  <div>
    <button type="button" class="form-input" @click="addPlayer">Add a player</button>

    <div class="grid" :class="'grid-cols-' + (players.length + 1)">
      <div class="mb-6">
        <p>Players</p>
      </div>
      <div v-for="(player, playerIndex) in players" class="relative">
        <input :value="player.name" type="text" class="form-input pl-10">
        <button type="button" class="absolute" style="left: 1rem;top: 6px" @click="removePlayer(playerIndex)">x</button>
        <br>
        <span v-text="getPlayerTotal(player)"></span>
      </div>
    </div>
    <div v-for="(round, roundIndex) in rounds" class="grid" :class="'grid-cols-' + (players.length + 1)">

      <div class="relative">
        <input type="text" class="form-input pl-10" :value="round">
        <button type="button" class="absolute" style="left: 1rem;top: 6px" @click="removeRound(roundIndex)">x</button>
      </div>

      <div v-for="(player, playerIndex) in players" class="relative">
        <input type="number" class="form-input pl-10" v-model.number="player.roundTotals[roundIndex]">
        <button type="button" class="absolute" style="left: 1rem;top: 6px" @click="halfIt(player,roundIndex)">½</button>
      </div>

    </div>
    <button type="button" class="form-input" @click="addRound">Add a round</button>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        rounds: [
          'Any Score',
          '20s',
          'Doubles',
          '19s',
          'Trebles',
          '18s',
          'Score 61 (3 different scoring darts)',
          '3 different colours',
          '25s',
          'Bullseyes'
        ],
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
      addPlayer() {
        this.players.push(
          {
            name: 'Player' + (this.players.length + 1),
            roundTotals: this.rounds.map(round => 0),
            halvedRounds: []
          },
        );
      },
      removePlayer(index) {
        this.players.splice(index, 1);
      },
      halfIt(player, roundIndex) {
        player.roundTotals[roundIndex] = -1 * Math.floor(this.getPlayerTotal(player) / 2);
        this.$forceUpdate();
      },
      getPlayerTotal(player) {
        return player.roundTotals.length > 0 ? player.roundTotals.reduce((accumulator, currentValue) => accumulator + currentValue) : 0;
      }
    }
  }
</script>

<style scoped>

</style>
