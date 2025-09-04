<template>
  <div class="n01-scorer">
    <!-- Game Setup Section -->
    <div v-if="!gameStarted" class="bg-gray-100 p-6 rounded-lg mb-6">
      <h2 class="text-3xl mb-4">Game Setup</h2>

      <!-- Game Options -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium mb-2">Default Target Score</label>
          <input
              type="number"
              v-model.number="gameSettings.defaultTarget"
              min="1"
              max="9999"
              class="w-full p-2 border rounded"
          >
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Board Type</label>
          <select v-model="gameSettings.boardType" class="w-full p-2 border rounded">
            <option value="standard">Standard (max 180)</option>
            <option value="quadro">Quadro (max 240)</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">In Type</label>
          <select v-model="gameSettings.inType" class="w-full p-2 border rounded">
            <option value="single">Single In</option>
            <option value="double">Double In</option>
            <option value="treble">Treble In</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Out Type</label>
          <select v-model="gameSettings.outType" class="w-full p-2 border rounded">
            <option value="double">Double Out</option>
            <option value="single">Single Out</option>
            <option value="royal">Royal Out</option>
          </select>
        </div>
      </div>

      <!-- Team Setup for 3+ players -->
      <div v-if="startPlayers.length >= 3" class="mb-6">
        <label class="flex items-center space-x-2 mb-4">
          <input type="checkbox" v-model="gameSettings.enableTeams">
          <span class="text-lg font-medium">Enable Team Play</span>
        </label>
      </div>

      <!-- Handicap Settings -->
      <div class="mb-6">
        <h3 class="text-xl mb-3">Player Target Scores (Handicap System)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="player in startPlayers" :key="player.id"
               class="flex items-center justify-between bg-white p-3 rounded">
            <label class="text-lg font-medium">{{ player.title }}:</label>
            <input
                type="number"
                v-model.number="playerTargets[player.id]"
                min="1"
                max="9999"
                class="w-24 p-2 border rounded"
            >
          </div>
        </div>
      </div>

      <button
          @click="startGame"
          :disabled="startPlayers.length === 0"
          class="bg-green-600 text-white px-6 py-3 rounded-lg text-lg font-medium hover:bg-green-700 transition-colors disabled:opacity-50"
      >
        Start Game
      </button>
    </div>

    <!-- Game Interface -->
    <div v-if="gameStarted">
      <!-- Current Player Display -->
      <div class="bg-blue-100 p-4 rounded-lg mb-6 text-center">
        <h2 class="text-2xl font-bold">{{ currentPlayer.name }} - {{ currentPlayer.currentScore }} remaining</h2>
        <p v-if="gameSettings.enableTeams && currentPlayer.team" class="text-lg">
          Playing for {{ currentPlayer.team === 'team1' ? 'Team 1' : 'Team 2' }}
        </p>
      </div>

      <!-- Scoreboard -->
      <div class="overflow-x-auto mb-6">
        <table class="w-full bg-white rounded-lg shadow">
          <thead class="bg-gray-50">
          <tr>
            <th class="p-3 text-left">Player</th>
            <th class="p-3 text-center">Target</th>
            <th class="p-3 text-center">Current Score</th>
            <th class="p-3 text-center">Darts</th>
            <th class="p-3 text-center">Average</th>
            <th v-if="gameSettings.enableTeams" class="p-3 text-center">Team</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="player in players" :key="player.id" :class="{ 'opacity-50': player.isOut }">
            <td class="p-3 font-medium">{{ player.name }}</td>
            <td class="p-3 text-center">{{ player.target }}</td>
            <td class="p-3 text-center" :class="{ 'bg-green-200 text-green-800': isOutPossible(player) }">
              {{ player.currentScore }}
            </td>
            <td class="p-3 text-center">{{ player.dartsThrown }}</td>
            <td class="p-3 text-center">{{ getPlayerAverage(player) }}</td>
            <td v-if="gameSettings.enableTeams" class="p-3 text-center">
              {{ player.team ? (player.team === 'team1' ? 'Team 1' : 'Team 2') : '-' }}
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Scoring Input -->
      <div class="bg-gray-100 p-6 rounded-lg mb-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-bold">Score Input</h3>
          <label class="flex items-center space-x-2">
            <input type="checkbox" v-model="perThrowMode">
            <span>Per Throw Mode</span>
          </label>
        </div>

        <!-- Simple Score Input -->
        <div v-if="!perThrowMode" class="space-y-4">
          <div class="flex items-center space-x-4">
            <label class="text-lg font-medium">Score:</label>
            <input
                type="number"
                v-model.number="scoreInput"
                :min="0"
                :max="maxScore"
                class="p-2 border rounded text-lg w-24"
                @input="validateScore"
            >
            <button
                @click="submitScore"
                :disabled="!isValidScore"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              Submit
            </button>
            <button
                @click="submitBust"
                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors"
            >
              Bust
            </button>
          </div>
          <p class="text-sm" :class="scoreValidationClass">{{ scoreValidationMessage }}</p>
        </div>

        <!-- Per Throw Input -->
        <div v-if="perThrowMode">
          <div class="grid grid-cols-3 gap-4 mb-4">
            <div v-for="(dart, index) in currentThrows" :key="index" class="text-center">
              <h4 class="font-medium mb-2">Dart {{ index + 1 }}</h4>
              <div class="p-4 border rounded bg-white min-h-[60px] flex items-center justify-center text-lg font-bold">
                {{ dart ? formatDartDisplay(dart) : '-' }}
              </div>
            </div>
          </div>

          <!-- Dartboard Input -->
          <div class="dartboard-input mb-4">
            <div class="grid grid-cols-5 gap-3 mb-4">
              <!-- Numbers 20 down to 1 (descending order) -->
              <div v-for="num in descendingNumbers" :key="num" class="number-group bg-white p-3 rounded border">
                <div class="text-center font-bold mb-2 text-lg">{{ num }}</div>
                <div class="grid grid-cols-2 gap-1">
                  <button @click="selectDart(num, 'single')" class="dart-btn single">S</button>
                  <button @click="selectDart(num * 2, 'double')" class="dart-btn double">D</button>
                  <button @click="selectDart(num * 3, 'treble')" class="dart-btn treble">T</button>
                  <button
                      v-if="gameSettings.boardType === 'quadro'"
                      @click="selectDart(num * 4, 'quad')"
                      class="dart-btn quad"
                  >Q</button>
                </div>
              </div>
            </div>

            <!-- Special segments -->
            <div class="flex justify-center space-x-4 mb-4">
              <button @click="selectDart(25, 'single')" class="dart-btn special-large">Bull 25</button>
              <button @click="selectDart(50, 'double')" class="dart-btn special-large">Bullseye 50</button>
              <button @click="selectDart(0, 'miss')" class="dart-btn miss-large">Miss</button>
            </div>
          </div>

          <div class="flex justify-center space-x-4">
            <button
                @click="submitThrows"
                :disabled="currentThrows.every(t => t === null)"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              Submit Throws
            </button>
            <button
                @click="clearThrows"
                class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition-colors"
            >
              Clear
            </button>
            <button
                @click="submitBust"
                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors"
            >
              Bust
            </button>
          </div>
        </div>
      </div>

      <!-- Game Controls -->
      <div class="flex justify-center space-x-4 mb-6">
        <button
            @click="undoLastScore"
            :disabled="gameHistory.length === 0"
            class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 transition-colors disabled:opacity-50"
        >
          Undo Last Score
        </button>
        <button
            @click="resetGame"
            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors"
        >
          Reset Game
        </button>
      </div>
    </div>

    <!-- Game Over Modal -->
    <div v-if="gameOver" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-8 rounded-lg max-w-md w-full mx-4">
        <h2 class="text-3xl font-bold text-center mb-4">Game Over!</h2>
        <div class="text-center text-xl mb-6">
          <h3 class="text-2xl font-bold">{{ winner.name }} Wins!</h3>
          <p>Final score: {{ winner.target }} in {{ winner.dartsThrown }} darts</p>
          <p v-if="winner.dartsThrown > 0">Average: {{ getPlayerAverage(winner) }}</p>
        </div>
        <div class="flex justify-center space-x-4">
          <button
              @click="newGame"
              class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition-colors"
          >
            New Game
          </button>
          <button
              @click="gameOver = false"
              class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700 transition-colors"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'N01Scorer',
  props: {
    startPlayers: {
      type: Array,
      default: () => []
    },
    game: {
      type: String,
      default: 'n01'
    }
  },
  data() {
    return {
      gameStarted: false,
      gameOver: false,
      winner: null,
      currentPlayerIndex: 0,
      perThrowMode: false,
      scoreInput: '',
      currentThrows: [null, null, null],
      currentThrowIndex: 0,
      gameHistory: [],

      gameSettings: {
        defaultTarget: 501,
        boardType: 'standard',
        inType: 'single',
        outType: 'double',
        enableTeams: false
      },

      playerTargets: {},
      players: [],

      // Score validation
      isValidScore: false,
      scoreValidationMessage: '',
      scoreValidationClass: 'text-gray-600'
    }
  },

  computed: {
    maxScore() {
      return this.gameSettings.boardType === 'quadro' ? 240 : 180;
    },

    currentPlayer() {
      return this.players[this.currentPlayerIndex] || {};
    },

    descendingNumbers() {
      // Generate an array of numbers from 20 to 1
      return Array.from({ length: 20 }, (_, i) => 20 - i);
    }
  },

  mounted() {
    // Initialize player targets with default values
    this.startPlayers.forEach(player => {
      // Vue 3: Direct assignment instead of this.$set
      this.playerTargets[player.id] = this.gameSettings.defaultTarget;
    });
  },

  methods: {
    startGame() {
      if (this.startPlayers.length === 0) {
        alert('Please select at least one player');
        return;
      }

      // Initialize players with their target scores
      this.players = this.startPlayers.map(player => ({
        id: player.id,
        name: player.title,
        target: this.playerTargets[player.id] || this.gameSettings.defaultTarget,
        currentScore: this.playerTargets[player.id] || this.gameSettings.defaultTarget,
        dartsThrown: 0,
        scores: [],
        hasStarted: false,
        isOut: false,
        team: null // Simplified team assignment for now
      }));

      this.gameStarted = true;
      this.currentPlayerIndex = 0;
    },

    validateScore() {
      const score = parseInt(this.scoreInput);
      const player = this.currentPlayer;

      if (isNaN(score) || score < 0) {
        this.isValidScore = false;
        this.scoreValidationMessage = 'Invalid score';
        this.scoreValidationClass = 'text-red-600';
        return;
      }

      if (!this.isValidDartScore(score)) {
        this.isValidScore = false;
        this.scoreValidationMessage = 'Impossible score with darts';
        this.scoreValidationClass = 'text-red-600';
        return;
      }

      const newScore = player.currentScore - score;

      if (this.isBustScore(newScore, score, player)) {
        this.isValidScore = false;
        this.scoreValidationMessage = 'This would be a bust';
        this.scoreValidationClass = 'text-red-600';
        return;
      }

      this.isValidScore = true;
      this.scoreValidationMessage = newScore === 0 ? 'Winning score!' : `Would leave ${newScore}`;
      this.scoreValidationClass = newScore === 0 ? 'text-green-600' : 'text-gray-600';
    },

    isValidDartScore(score) {
      if (score === 0) return true;

      // Generate all possible dart scores
      const possibleScores = this.generatePossibleScores();
      return possibleScores.has(score);
    },

    generatePossibleScores() {
      const scores = new Set([0]);
      const singleScores = [];

      // Singles 1-20, doubles, trebles, and quads if enabled
      for (let i = 1; i <= 20; i++) {
        singleScores.push(i, i * 2, i * 3);
        if (this.gameSettings.boardType === 'quadro') {
          singleScores.push(i * 4);
        }
      }
      singleScores.push(25, 50); // Bull and bullseye

      // Generate all combinations for 1, 2, and 3 darts
      singleScores.forEach(s => scores.add(s));

      for (let i = 0; i < singleScores.length; i++) {
        for (let j = 0; j < singleScores.length; j++) {
          scores.add(singleScores[i] + singleScores[j]);
        }
      }

      for (let i = 0; i < singleScores.length; i++) {
        for (let j = 0; j < singleScores.length; j++) {
          for (let k = 0; k < singleScores.length; k++) {
            const total = singleScores[i] + singleScores[j] + singleScores[k];
            if (total <= this.maxScore) {
              scores.add(total);
            }
          }

        }
      }

      return scores;
    },

    isBustScore(newScore, scoreThrown, player) {
      if (newScore < 0) return true;

      // Check in/out rules
      if (!player.hasStarted && !this.isValidInScore(scoreThrown)) {
        return true;
      }

      if (newScore === 0 && !this.isValidOutScore(scoreThrown)) {
        return true;
      }

      // Double out: can't finish on 1
      if (this.gameSettings.outType === 'double' && newScore === 1) {
        return true;
      }

      return false;
    },

    isValidInScore(score) {
      if (this.gameSettings.inType === 'single') return true;
      if (this.gameSettings.inType === 'double') {
        return this.containsDouble(score) || score === 50;
      }
      if (this.gameSettings.inType === 'treble') {
        return this.containsTreble(score);
      }
      return true;
    },

    isValidOutScore(score) {
      if (this.gameSettings.outType === 'single') return true;
      if (this.gameSettings.outType === 'double') {
        return this.containsDouble(score) || score === 50;
      }
      if (this.gameSettings.outType === 'royal') {
        return this.containsTreble(score) || this.containsDouble(score) ||
            (this.gameSettings.boardType === 'quadro' && this.containsQuad(score));
      }
      return true;
    },

    containsDouble(score) {
      for (let i = 1; i <= 20; i++) {
        if (score === i * 2) return true;
      }
      return false;
    },

    containsTreble(score) {
      for (let i = 1; i <= 20; i++) {
        if (score === i * 3) return true;
      }
      return false;
    },

    containsQuad(score) {
      for (let i = 1; i <= 20; i++) {
        if (score === i * 4) return true;
      }
      return false;
    },

    isOutPossible(player) {
      const remaining = player.currentScore;

      if (remaining <= 0) return false;
      if (remaining > this.maxScore) return false;

      // Check out rules
      if (this.gameSettings.outType === 'double') {
        if (remaining % 2 !== 0 && remaining !== 25 && remaining !== 50) return false;
        if (remaining > 40 && remaining !== 50) return false;
      }

      return true;
    },

    getPlayerAverage(player) {
      if (player.dartsThrown === 0) return '0.0';
      return ((player.target - player.currentScore) / player.dartsThrown * 3).toFixed(1);
    },

    selectDart(value, type) {
      if (this.currentThrowIndex >= 3) return;

      // Vue 3: Direct assignment to reactive array instead of this.$set
      this.currentThrows[this.currentThrowIndex] = { value, type };
      this.currentThrowIndex++;
    },

    formatDartDisplay(dart) {
      if (dart.type === 'miss') return 'Miss';
      if (dart.type === 'single') return dart.value.toString();

      const baseValue = dart.value / (dart.type === 'double' ? 2 : dart.type === 'treble' ? 3 : dart.type === 'quad' ? 4 : 1);
      return `${dart.type.charAt(0).toUpperCase()}${baseValue}`;
    },

    clearThrows() {
      this.currentThrows = [null, null, null];
      this.currentThrowIndex = 0;
    },

    submitScore() {
      if (!this.isValidScore) return;

      const score = parseInt(this.scoreInput);
      this.processScore(score, false);
      this.scoreInput = '';
    },

    submitThrows() {
      const totalScore = this.currentThrows
          .filter(t => t !== null)
          .reduce((sum, t) => sum + t.value, 0);

      this.processScore(totalScore, false);
      this.clearThrows();
    },

    submitBust() {
      this.processScore(0, true);
      if (this.perThrowMode) {
        this.clearThrows();
      } else {
        this.scoreInput = '';
      }
    },

    processScore(score, isBust) {
      const player = this.currentPlayer;
      const dartsUsed = this.perThrowMode ?
          this.currentThrows.filter(t => t !== null).length : 3;

      // Save to history for undo
      this.gameHistory.push({
        playerIndex: this.currentPlayerIndex,
        previousScore: player.currentScore,
        score: score,
        isBust: isBust,
        dartsUsed: dartsUsed,
        hadStarted: player.hasStarted
      });

      if (!isBust) {
        const newScore = player.currentScore - score;

        if (this.isBustScore(newScore, score, player)) {
          isBust = true;
        }
        else {
          player.currentScore = newScore;
          player.scores.push(score);

          if (!player.hasStarted && score > 0) {
            player.hasStarted = true;
          }

          if (newScore === 0) {
            this.playerFinished(player);
            return;
          }
        }
      }

      player.dartsThrown += dartsUsed;

      if (isBust) {
        player.scores.push(`Bust (${score})`);
      }

      this.nextPlayer();
    },

    playerFinished(player) {
      player.isOut = true;
      this.winner = player;
      this.gameOver = true;
    },

    nextPlayer() {
      do {
        this.currentPlayerIndex = (this.currentPlayerIndex + 1) % this.players.length;
      } while (this.players[this.currentPlayerIndex].isOut);
    },

    undoLastScore() {
      if (this.gameHistory.length === 0) return;

      const lastMove = this.gameHistory.pop();
      const player = this.players[lastMove.playerIndex];

      // Restore player state
      player.currentScore = lastMove.previousScore;
      player.dartsThrown -= lastMove.dartsUsed;
      player.hasStarted = lastMove.hadStarted;
      player.isOut = false;
      player.scores.pop();

      // Go back to that player
      this.currentPlayerIndex = lastMove.playerIndex;

      // Close game over modal if it was open
      this.gameOver = false;
      this.winner = null;
    },

    newGame() {
      this.resetGame();
    },

    resetGame() {
      this.gameStarted = false;
      this.gameOver = false;
      this.winner = null;
      this.currentPlayerIndex = 0;
      this.players = [];
      this.gameHistory = [];
      this.scoreInput = '';
      this.clearThrows();

      // Reset player targets to default - Vue 3 compatible
      this.startPlayers.forEach(player => {
        this.playerTargets[player.id] = this.gameSettings.defaultTarget;
      });
    }
  }
}
</script>

<style scoped>
.dart-btn {
  @apply px-3 py-2 text-sm rounded transition-colors cursor-pointer font-medium;
}

.dart-btn.single {
  @apply bg-green-100 hover:bg-green-200 text-green-800;
}

.dart-btn.double {
  @apply bg-red-100 hover:bg-red-200 text-red-800;
}

.dart-btn.treble {
  @apply bg-yellow-100 hover:bg-yellow-200 text-yellow-800;
}

.dart-btn.quad {
  @apply bg-purple-100 hover:bg-purple-200 text-purple-800;
}

.dart-btn.special {
  @apply bg-blue-100 hover:bg-blue-200 text-blue-800;
}

.dart-btn.special-large {
  @apply px-4 py-3 text-base rounded transition-colors cursor-pointer font-medium bg-blue-100 hover:bg-blue-200 text-blue-800;
}

.dart-btn.miss {
  @apply bg-gray-100 hover:bg-gray-200 text-gray-800;
}

.dart-btn.miss-large {
  @apply px-4 py-3 text-base rounded transition-colors cursor-pointer font-medium bg-gray-100 hover:bg-gray-200 text-gray-800;
}
</style>