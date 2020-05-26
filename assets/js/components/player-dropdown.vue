<template>
  <select class="form-select" @change="changed" v-model="selectedPlayerId">
    <option :value="null" disabled selected>Please Select</option>
    <option :value="player.id" v-for="player in players" v-text="player.title"></option>
  </select>
</template>

<script>
  export default {
    name: "player-dropdown",
    props: {
      players: {
        required: true,
        type: Array
      }
    },
    data() {
      return {
        selectedPlayerId: null
      }
    },
    methods: {
      changed() {
        let player = this.players.filter(comp => comp.id === this.selectedPlayerId);
        this.$emit('player-change', player[0])
      }
    }
  }
</script>

<style scoped>

</style>
