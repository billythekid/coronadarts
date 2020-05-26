<template>
  <select class="form-select" @change="changed" v-model="selectedCompetitionId">
    <option :value="null" disabled selected>Please Select</option>
    <option :value="competition.id" v-for="competition in competitions" v-text="competition.title"></option>
  </select>
</template>

<script>
  export default {
    name: "competitions-dropdown",
    props: {
      competitions: {
        required: true,
        type: Array
      }
    },
    data() {
      return {
        selectedCompetitionId: null
      }
    },
    methods: {
      changed() {
        let competition = this.competitions.filter(comp => comp.id === this.selectedCompetitionId);
        this.$emit('competition-change', competition[0])
      }
    }

  }
</script>

<style scoped>

</style>
