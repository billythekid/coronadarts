<template>
  <div class="n01-scorer" :class="{ 'fullscreen-overlay': gameStarted }">
    <!-- Game Setup Section -->
    <div v-if="!gameStarted" class="bg-gray-50 p-6 rounded-lg mb-6 ring-2 ring-blue-800">
      <h2 class="text-2xl mb-4 text-sky-800">Game Setup</h2>

      <!-- Player Management -->
      <div class="mb-6">
        <h3 class="text-xl mb-3 text-sky-800">Players</h3>

        <!-- Show selected players from scorers page if any -->
        <div v-if="startPlayers.length > 0" class="mb-4">
          <h4 class="text-lg font-medium mb-2 text-sky-700">Selected Players:</h4>
          <div class="flex flex-wrap gap-2 mb-3">
            <span v-for="player in startPlayers" :key="player.id"
                  class="bg-sky-100 text-sky-800 px-3 py-1 rounded-full ring-1 ring-sky-300">
              {{ player.title }}
            </span>
          </div>
        </div>

        <!-- Add custom players section -->
        <div class="bg-white p-4 rounded border ring-1 ring-sky-200">
          <h4 class="text-lg font-medium mb-3 text-sky-700">Add Visiting Players</h4>

          <!-- Input for new player -->
          <div class="flex items-center space-x-3 mb-3">
            <input
              type="text"
              v-model="newPlayerName"
              placeholder="Enter player name"
              @keyup.enter="addCustomPlayer"
              class="flex-1 p-2 border rounded ring-1 ring-sky-200 focus:ring-blue-600"
            >
            <button
              @click="addCustomPlayer"
              :disabled="!newPlayerName.trim()"
              class="bg-sky-600 text-white px-4 py-2 rounded hover:bg-sky-700 transition-colors disabled:opacity-50"
            >
              Add Player
            </button>
          </div>

          <!-- List of custom players -->
          <div v-if="customPlayers.length > 0" class="space-y-2">
            <h5 class="font-medium text-sky-700">Visiting Players:</h5>
            <div class="flex flex-wrap gap-2">
              <div v-for="(player, index) in customPlayers" :key="'custom-' + index"
                   class="flex items-center bg-sky-100 text-sky-800 px-3 py-1 rounded-full ring-1 ring-sky-300">
                <span>{{ player.name }}</span>
                <button
                  @click="removeCustomPlayer(index)"
                  class="ml-2 text-sky-600 hover:text-sky-800 font-bold"
                  title="Remove player"
                >
                  ×
                </button>
              </div>
            </div>
          </div>

          <!-- Total player count -->
          <div v-if="totalPlayers > 0" class="mt-3 text-sm text-sky-600">
            Total players: {{ totalPlayers }}
          </div>
        </div>
      </div>

      <!-- Game Options -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium mb-2 text-sky-700">Default Target Score</label>
          <div class="space-y-3">
            <!-- Preset target score buttons -->
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="score in [101, 201, 301, 501, 701, 1001]"
                :key="score"
                @click="selectTargetScore(score)"
                :class="[
                  'px-3 py-2 text-sm font-medium rounded border transition-colors',
                  gameSettings.defaultTarget === score && gameSettings.targetScoreType === 'preset'
                    ? 'bg-blue-600 text-white border-blue-600'
                    : 'bg-gray-50 text-sky-800 border-sky-300 hover:bg-sky-50 ring-1 ring-sky-200'
                ]"
              >
                {{ score }}
              </button>
            </div>

            <!-- Other button -->
            <button
              @click="selectTargetScore('other')"
              :class="[
                'w-full px-3 py-2 text-sm font-medium rounded border transition-colors',
                gameSettings.targetScoreType === 'other'
                  ? 'bg-blue-600 text-white border-blue-600'
                  : 'bg-gray-50 text-sky-800 border-sky-300 hover:bg-sky-50 ring-1 ring-sky-200'
              ]"
            >
              Other
            </button>

            <!-- Custom input (shown only when "Other" is selected) -->
            <input
              v-if="gameSettings.targetScoreType === 'other'"
              type="number"
              v-model.number="gameSettings.customTarget"
              min="1"
              max="9999"
              placeholder="Enter custom score"
              class="w-full p-2 border rounded ring-1 ring-sky-200 focus:ring-blue-600"
            >
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2 text-sky-700">Board Type</label>
          <select v-model="gameSettings.boardType" class="w-full p-2 border rounded ring-1 ring-sky-200 focus:ring-blue-600 bg-white">
            <option value="standard">Standard (max 180)</option>
            <option value="quadro">Quadro (max 240)</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2 text-sky-700">In Type</label>
          <select v-model="gameSettings.inType" class="w-full p-2 border rounded ring-1 ring-sky-200 focus:ring-blue-600 bg-white">
            <option value="single">Single In</option>
            <option value="double">Double In</option>
            <option value="treble">Treble In</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2 text-sky-700">Out Type</label>
          <select v-model="gameSettings.outType" class="w-full p-2 border rounded ring-1 ring-sky-200 focus:ring-blue-600 bg-white">
            <option value="double">Double Out</option>
            <option value="single">Single Out</option>
            <option value="royal">Royal Out</option>
          </select>
        </div>
      </div>

      <!-- Sets and Legs Configuration -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Sets Configuration -->
        <div class="bg-white p-4 rounded border ring-1 ring-sky-200">
          <h4 class="text-lg font-medium mb-3 text-sky-700">Sets</h4>
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium mb-1 text-sky-700">Format</label>
              <select v-model="gameSettings.setsFormat" @change="updateSetsNumber" class="w-full p-2 border rounded ring-1 ring-sky-200 focus:ring-blue-600 bg-white">
                <option value="bestOf">Best of</option>
                <option value="firstTo">First to</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1 text-sky-700">{{ gameSettings.setsFormat === 'bestOf' ? 'Best of' : 'First to' }}</label>
              <select v-model.number="gameSettings.setsNumber" class="w-full p-2 border rounded ring-1 ring-sky-200 focus:ring-blue-600 bg-white">
                <option v-for="num in getValidSetsNumbers()" :key="num" :value="num">{{ num }}</option>
              </select>
            </div>
            <div class="text-sm text-sky-600">
              {{ getSetsDescription() }}
            </div>
          </div>
        </div>

        <!-- Legs Configuration -->
        <div class="bg-white p-4 rounded border ring-1 ring-sky-200">
          <h4 class="text-lg font-medium mb-3 text-sky-700">Legs</h4>
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium mb-1 text-sky-700">Format</label>
              <select v-model="gameSettings.legsFormat" @change="updateLegsNumber" class="w-full p-2 border rounded ring-1 ring-sky-200 focus:ring-blue-600 bg-white">
                <option value="bestOf">Best of</option>
                <option value="firstTo">First to</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1 text-sky-700">{{ gameSettings.legsFormat === 'bestOf' ? 'Best of' : 'First to' }}</label>
              <select v-model.number="gameSettings.legsNumber" class="w-full p-2 border rounded ring-1 ring-sky-200 focus:ring-blue-600 bg-white">
                <option v-for="num in getValidLegsNumbers()" :key="num" :value="num">{{ num }}</option>
              </select>
            </div>
            <div class="text-sm text-sky-600">
              {{ getLegsDescription() }}
            </div>
          </div>
        </div>
      </div>

      <!-- Team Setup for 3+ players -->
      <div v-if="totalPlayers >= 3" class="mb-6">
        <label class="flex items-center space-x-2 mb-4">
          <input type="checkbox" v-model="gameSettings.enableTeams" @change="onTeamModeToggle" class="rounded border-sky-300 text-blue-600 focus:ring-blue-500">
          <span class="text-lg font-medium text-sky-800">Enable Team Play</span>
        </label>

        <!-- Team Configuration -->
        <div v-if="gameSettings.enableTeams" class="space-y-4">
          <!-- Number of Teams -->
          <div class="flex items-center space-x-4">
            <label class="text-lg font-medium text-sky-800">Number of Teams:</label>
            <select v-model.number="gameSettings.numberOfTeams" @change="onTeamCountChange" class="p-2 border rounded ring-1 ring-sky-200 focus:ring-blue-600 bg-white">
              <option value="2">2 Teams</option>
              <option value="3">3 Teams</option>
              <option value="4">4 Teams</option>
            </select>
          </div>

          <!-- Team Assignment Area -->
          <div class="grid gap-4" :class="getTeamGridClass()">
            <div v-for="(team, teamIndex) in teams" :key="teamIndex"
                 class="team-drop-zone border-2 border-dashed border-sky-300 rounded-lg p-4 min-h-[200px] bg-sky-50"
                 :class="getTeamColorClass(teamIndex + 1)"
                 @drop="onDrop($event, teamIndex)"
                 @dragover.prevent
                 @dragenter.prevent>
              <!-- Team Name -->
              <div class="mb-3">
                <input
                  v-model="team.name"
                  :placeholder="`Team ${teamIndex + 1}`"
                  class="w-full p-2 border rounded font-medium text-center ring-1 ring-sky-200 focus:ring-blue-600"
                >
              </div>

              <!-- Team Score (if team has players) -->
              <div v-if="team.players.length > 0" class="mb-2 text-center">
                <div class="text-sm text-sky-600">Team Score</div>
                <div class="text-lg font-bold text-sky-800">{{ getTeamScore(teamIndex) }}</div>
              </div>

              <!-- Team Players -->
              <div class="space-y-2">
                <h5 class="font-medium text-sm text-sky-600 text-center">
                  Players ({{ team.players.length }})
                </h5>
                <div v-for="(player, playerIndex) in team.players"
                     :key="player.id || `${player.name}-${playerIndex}`"
                     class="bg-white p-2 rounded shadow-sm border ring-1 ring-sky-200 flex items-center justify-between">
                  <span class="font-medium text-sky-800">{{ player.name }}</span>
                  <div class="flex items-center space-x-2">
                    <span class="text-xs text-sky-500">#{{ playerIndex + 1 }}</span>
                    <button @click="removePlayerFromTeam(teamIndex, playerIndex)"
                            class="text-sky-600 hover:text-sky-800 text-sm">×</button>
                  </div>
                </div>
                <p v-if="team.players.length === 0"
                   class="text-sky-400 text-sm text-center italic">
                  Drag players here
                </p>
              </div>
            </div>
          </div>

          <!-- Available Players -->
          <div class="mt-6">
            <h4 class="text-lg font-medium mb-3 text-sky-700">Available Players</h4>
            <div class="flex flex-wrap gap-2 p-4 border-2 border-dashed border-sky-200 rounded-lg min-h-[80px] bg-sky-50">
              <!-- Selected Players from scorers page -->
              <div v-for="player in unassignedStartPlayers" :key="player.id"
                   class="player-card bg-sky-100 text-sky-800 px-3 py-2 rounded-full cursor-move flex items-center ring-1 ring-sky-300"
                   draggable="true"
                   @dragstart="onDragStart($event, { ...player, name: player.title, type: 'selected' })">
                <span>{{ player.title }}</span>
              </div>

              <!-- Custom/visiting players -->
              <div v-for="(player, index) in unassignedCustomPlayers" :key="'custom-' + index"
                   class="player-card bg-sky-100 text-sky-800 px-3 py-2 rounded-full cursor-move flex items-center ring-1 ring-sky-300"
                   draggable="true"
                   @dragstart="onDragStart($event, { ...player, type: 'custom', originalIndex: index })">
                <span>{{ player.name }}</span>
              </div>

              <p v-if="unassignedStartPlayers.length === 0 && unassignedCustomPlayers.length === 0"
                 class="text-sky-400 text-sm italic w-full text-center">
                All players assigned to teams
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Handicap Settings -->
      <div v-if="totalPlayers > 0 && !gameSettings.enableTeams" class="mb-6">
        <h3 class="text-xl mb-3 text-sky-800">Player Target Scores (Handicap System)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- Selected players from scorers page -->
          <div v-for="player in startPlayers" :key="player.id"
               class="flex items-center justify-between bg-white p-3 rounded ring-1 ring-sky-200">
            <label class="text-lg font-medium text-sky-800">{{ player.title }}:</label>
            <input
                type="number"
                v-model.number="playerTargets[player.id]"
                min="1"
                max="9999"
                class="w-24 p-2 border rounded ring-1 ring-sky-200 focus:ring-blue-600"
            >
          </div>
          <!-- Custom/visiting players -->
          <div v-for="(player, index) in customPlayers" :key="'custom-target-' + index"
               class="flex items-center justify-between bg-sky-50 p-3 rounded border-sky-200 ring-1 ring-sky-200">
            <label class="text-lg font-medium text-sky-800">{{ player.name }}:</label>
            <input
                type="number"
                v-model.number="player.target"
                min="1"
                max="9999"
                class="w-24 p-2 border rounded ring-1 ring-sky-200 focus:ring-blue-600"
            >
          </div>
        </div>
      </div>

      <button
          @click="startGame"
          :disabled="totalPlayers === 0"
          class="bg-sky-600 text-white px-6 py-3 rounded-lg text-lg font-medium hover:bg-sky-700 transition-colors disabled:opacity-50"
      >
        Start Game
      </button>
    </div>

    <!-- Game Interface - Full Screen Overlay When Playing -->
    <div v-if="gameStarted" class="game-interface">

      <!-- Current Player Display - TOP PRIORITY -->
      <div class="current-player-display">
        <!-- Player Carousel -->
        <div class="player-carousel">
          <div class="carousel-container" :style="carouselTransform">
            <div v-for="player in players" :key="player.id"
                 class="player-slide"
                 :class="{
                   'current': isCurrentPlayer(player),
                   'prev': isPreviousPlayer(player),
                   'next': isNextPlayer(player),
                   'distant': isDistantPlayer(player)
                 }">
              <div class="player-content">
                <h2 class="player-name">{{ player.name }}</h2>
                <div class="remaining-score">{{ player.currentScore }}</div>
                <p v-if="gameSettings.enableTeams && player.teamName" class="team-name">
                  {{ player.teamName }}
                </p>
                <div v-if="isCurrentPlayer(player)" class="current-indicator">
                  <span class="indicator-text">Current Player</span>
                </div>
                <div v-else class="player-stats">
                  <span class="darts-thrown">{{ player.dartsThrown }} darts</span>
                  <span class="average">{{ getPlayerAverage(player) }} avg</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Scoring Input - SECOND PRIORITY -->
      <div class="scoring-section">
        <div class="mode-toggle">
          <label class="flex items-center space-x-2">
            <input type="checkbox" v-model="perThrowMode" class="rounded border-sky-300 text-blue-600 focus:ring-blue-500">
            <span class="text-sky-800">Per Throw Mode</span>
          </label>
        </div>

        <!-- Simple Score Input -->
        <div v-if="!perThrowMode" class="simple-scoring">
          <div class="score-input-row">
            <input
                ref="scoreInputField"
                type="number"
                v-model.number="scoreInput"
                :min="0"
                :max="maxScore"
                class="score-input"
                @input="validateScore"
                @keyup.enter="submitScore"
                @focus="onScoreInputFocus"
                placeholder="Enter score"
            >
            <button
                @click="submitScore"
                :disabled="!isValidScore"
                class="submit-btn"
            >
              Submit
            </button>
            <button
                @click="submitBust"
                class="bust-btn"
            >
              Bust
            </button>
          </div>
          <p class="validation-message" :class="scoreValidationClass">{{ scoreValidationMessage }}</p>
        </div>

        <!-- Per Throw Input -->
        <div v-if="perThrowMode" class="per-throw-scoring">
          <div class="darts-display">
            <div v-for="(dart, index) in currentThrows" :key="index" class="dart-slot">
              <span class="dart-label">{{ index + 1 }}</span>
              <div class="dart-value">
                {{ dart ? formatDartDisplay(dart) : '-' }}
              </div>
            </div>
          </div>

          <!-- Mobile-Friendly Dartboard Input -->
          <div class="dartboard-input">
            <div class="numbers-grid">
              <button
                v-for="num in sortedNumbers"
                :key="num"
                @click="showMultiplierPopover(num)"
                class="number-btn"
              >
                {{ num }}
              </button>
            </div>

            <div class="special-buttons">
              <button @click="addDartScore(25, 'single')" class="special-btn">25</button>
              <button @click="addDartScore(50, 'bull')" class="special-btn">Bull</button>
              <button @click="addDartScore(0, 'miss')" class="special-btn">Miss</button>
            </div>
          </div>

          <div class="throw-controls">
            <button
                @click="submitThrows"
                :disabled="currentThrows.every(t => t === null)"
                class="submit-btn"
            >
              Submit Throws
            </button>
            <button @click="clearThrows" class="clear-btn">Clear</button>
            <button @click="submitBust" class="bust-btn">Bust</button>
          </div>
        </div>
      </div>

      <!-- Game Controls - Quick Access -->
      <div class="quick-controls">
        <button @click="undoLastScore" :disabled="gameHistory.length === 0" class="undo-btn">
          Undo
        </button>
        <button @click="toggleStatsView" class="stats-btn">
          {{ showStats ? 'Hide Stats' : 'Show Stats' }}
        </button>
        <button @click="resetGame" class="reset-btn">Reset</button>
      </div>

      <!-- Collapsible Stats Section -->
      <div v-if="showStats" class="stats-section">
        <!-- Team-based Scoreboard -->
        <div v-if="gameSettings.enableTeams" class="team-scoreboard">
          <div v-for="team in activeTeams" :key="team.id" class="team-card">
            <div class="team-header">
              <h3 class="text-sky-800">{{ team.name }}</h3>
              <div class="team-score">
                <span class="score text-sky-800">{{ team.currentScore }}</span>
                <span class="details text-sky-600">{{ team.dartsThrown }} darts | {{ getTeamAverage(team) }} avg</span>
              </div>
            </div>
            <div class="team-players">
              <div v-for="player in getTeamPlayers(team)" :key="player.id"
                   class="player-row" :class="{ 'current': isCurrentPlayer(player), 'next': isUpNextPlayer(player) }">
                <span class="name text-sky-800">{{ player.name }}</span>
                <span class="darts text-sky-600">{{ player.dartsThrown }}</span>
                <span class="avg text-sky-600">{{ getPlayerAverage(player) }}</span>
                <span class="status">
                  <span v-if="player.isOut" class="out text-sky-500">Out</span>
                  <span v-else-if="isCurrentPlayer(player)" class="playing text-blue-600">Playing</span>
                  <span v-else-if="isUpNextPlayer(player)" class="next text-sky-700">Next</span>
                  <span v-else class="waiting text-sky-500">Waiting</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Individual Scoreboard -->
        <div v-else class="individual-scoreboard">
          <!-- Header row -->
          <div class="scoreboard-header">
            <div class="header-cell">Player</div>
            <div class="header-cell">Score</div>
            <div class="header-cell">Darts</div>
            <div class="header-cell">Average</div>
          </div>

          <!-- Player rows -->
          <div v-for="player in players" :key="player.id"
               class="player-row"
               :class="{ 'current-player-row': isCurrentPlayer(player), 'out-player-row': player.isOut }">
            <div class="player-cell name-cell text-sky-800">{{ player.name }}</div>
            <div class="player-cell score-cell text-sky-800"
                 :class="[
                   { 'finish-score': isValidFinish(player) },
                   { 'bogey-score': isBogeyFinish(player) }
                 ]">
              {{ player.currentScore }}
            </div>
            <div class="player-cell darts-cell text-sky-600">{{ player.dartsThrown }}</div>
            <div class="player-cell average-cell text-sky-600">{{ getPlayerAverage(player) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Game Over Modal -->
    <div v-if="gameOver" class="game-over-modal">
      <div class="modal-content">
        <h2 class="text-sky-800">Game Over!</h2>
        <h3 class="text-sky-700">{{ winner.name }} Wins!</h3>
        <p class="text-sky-600">Final score: {{ winner.target }} in {{ winner.dartsThrown }} darts</p>
        <p v-if="winner.dartsThrown > 0" class="text-sky-600">Average: {{ getPlayerAverage(winner) }}</p>
        <div class="modal-buttons">
          <button @click="newGame" class="new-game-btn">New Game</button>
          <button @click="gameOver = false" class="close-btn">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from 'sweetalert2'

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

      // Carousel animation state
      isTransitioning: false,

      gameSettings: {
        defaultTarget: 501,
        boardType: 'standard',
        inType: 'single',
        outType: 'double',
        enableTeams: false,
        numberOfTeams: 2,
        setsFormat: 'bestOf',
        setsNumber: 1,
        legsFormat: 'bestOf',
        legsNumber: 3,
        targetScoreType: 'preset',
        customTarget: 0
      },

      playerTargets: {},
      players: [],
      newPlayerName: '',
      customPlayers: [],
      teams: [],

      // Score validation
      isValidScore: false,
      scoreValidationMessage: '',
      scoreValidationClass: 'text-gray-600',

      // New state variables for mobile-friendly dartboard
      selectedNumber: null,
      sortedNumbers: [],

      // For collapsible stats section
      showStats: false
    }
  },

  computed: {
    maxScore() {
      return this.gameSettings.boardType === 'quadro' ? 240 : 180;
    },


    currentPlayer() {
      return this.players[this.currentPlayerIndex] || {};
    },

    totalPlayers() {
      return this.startPlayers.length + this.customPlayers.length;
    },

    unassignedStartPlayers() {
      if (!this.gameSettings.enableTeams) {
        return this.startPlayers;
      }
      // Players from startPlayers not assigned to any team
      return this.startPlayers.filter(player =>
        !this.teams.some(team =>
          team.players.some(p => p.id === player.id)
        )
      );
    },

    unassignedCustomPlayers() {
      if (!this.gameSettings.enableTeams) {
        return this.customPlayers;
      }
      // Custom players not assigned to any team
      return this.customPlayers.filter(player =>
        !this.teams.some(team =>
          team.players.some(p => p.id === `custom-${player.name}`)
        )
      );
    },


    activeTeams() {
      // Only include teams that have players assigned
      return this.teams.filter(team => team.players.length > 0);
    },

    carouselTransform() {
      if (this.players.length === 0) return { transform: 'translateX(0px)' };

      const cardWidth = 266; // 250px + 16px margin
      const containerCenter = window.innerWidth / 2;

      // Always center the current player
      // Offset = center position - (current player position * card width)
      const baseOffset = containerCenter - (cardWidth / 2) - (this.currentPlayerIndex * cardWidth);

      return {
        transform: `translateX(${baseOffset}px)`,
        transition: this.isTransitioning ? 'transform 0.6s ease-in-out' : 'transform 0.3s ease-out'
      };
    }
  },

  mounted() {
    // Initialize player targets with default values
    this.startPlayers.forEach(player => {
      // Vue 3: Direct assignment instead of this.$set
      this.playerTargets[player.id] = this.gameSettings.defaultTarget;
    });

    // Initialize teams
    this.initializeTeams();

    // Initialize sorted numbers for mobile-friendly dartboard
    this.sortedNumbers = Array.from({ length: 20 }, (_, i) => i + 1);
  },

  watch: {
    perThrowMode(newValue) {
      // Focus the input field when switching to simple mode
      if (!newValue) {
        this.focusScoreInput();
      }
    },

    // Watch for changes to default target score and update all players/teams
    'gameSettings.defaultTarget'(newValue, oldValue) {
      if (newValue !== oldValue && newValue > 0) {
        this.updateAllTargetScores(newValue);
      }
    },

    // Watch for changes to custom target input
    'gameSettings.customTarget'(newValue, oldValue) {
      if (this.gameSettings.targetScoreType === 'other' && newValue !== oldValue && newValue > 0) {
        this.gameSettings.defaultTarget = newValue;
        this.updateAllTargetScores(newValue);
      }
    }
  },

  methods: {
    startGame() {
      if (this.totalPlayers === 0) {
        alert('Please select at least one player');
        return;
      }

      if (this.gameSettings.enableTeams) {
        // Validate team setup
        const teamsWithPlayers = this.teams.filter(team => team.players.length > 0);

        if (teamsWithPlayers.length < 2) {
          alert('Please assign players to at least 2 teams');
          return;
        }

        // Create players from teams with proper team assignment and turn order
        this.players = this.createTeamPlayers(teamsWithPlayers);
      } else {
        // Regular individual play
        this.players = this.createIndividualPlayers();
      }

      this.gameStarted = true;
      this.currentPlayerIndex = 0;

      // Focus the input field when game starts
      this.focusScoreInput();
    },

    createIndividualPlayers() {
      return [
        // Selected players from scorers page
        ...this.startPlayers.map(player => ({
          id: player.id,
          name: player.title,
          target: this.playerTargets[player.id] || this.gameSettings.defaultTarget,
          currentScore: this.playerTargets[player.id] || this.gameSettings.defaultTarget,
          dartsThrown: 0,
          scores: [],
          hasStarted: false,
          isOut: false,
          team: null,
          teamName: null
        })),
        // Custom/visiting players
        ...this.customPlayers.map(player => ({
          id: `custom-${player.name}`,
          name: player.name,
          target: player.target || this.gameSettings.defaultTarget,
          currentScore: player.target || this.gameSettings.defaultTarget,
          dartsThrown: 0,
          scores: [],
          hasStarted: false,
          isOut: false,
          team: null,
          teamName: null
        }))
      ];
    },

    createTeamPlayers(teamsWithPlayers) {
      const players = [];

      // Initialize team scores and player tracking
      teamsWithPlayers.forEach(team => {
        team.currentScore = this.gameSettings.defaultTarget;
        team.target = this.gameSettings.defaultTarget;
        team.dartsThrown = 0;
        team.hasStarted = false;
        team.isOut = false;
        team.currentPlayerIndex = 0; // Track which player in the team is currently throwing
      });

      // Simple approach: just add all players with team info
      // The turn logic will be handled in nextPlayer() method
      teamsWithPlayers.forEach((team, teamIndex) => {
        team.players.forEach((teamPlayer, playerIndex) => {
          players.push({
            id: teamPlayer.id,
            name: teamPlayer.name,
            target: team.target,
            currentScore: team.currentScore,
            dartsThrown: 0,
            totalScoreThrown: 0, // Initialize individual scoring tracker
            scores: [],
            hasStarted: false,
            isOut: false,
            team: team.id,
            teamName: team.name,
            teamIndex: teamIndex,
            teamPlayerIndex: playerIndex,
            teamObject: team
          });
        });
      });

      return players;
    },

    validateScore() {
      const score = parseInt(this.scoreInput);
      const player = this.currentPlayer;

      // Check for empty input or invalid numbers first
      if (this.scoreInput === '' || this.scoreInput === null || this.scoreInput === undefined) {
        this.isValidScore = false;
        this.scoreValidationMessage = 'Please enter a score';
        this.scoreValidationClass = 'text-red-600';
        return;
      }

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

      // For finishing throws (newScore === 0), we need to handle per-throw mode differently from simple input
      if (newScore === 0) {
        // In per-throw mode, we can validate the actual finishing dart
        if (this.perThrowMode) {
          // Check if the last dart thrown is a valid finishing dart
          const lastDart = this.currentThrows.filter(t => t !== null).pop();
          if (lastDart && !this.isValidFinishingDart(lastDart)) {
            return true;
          }
        } else {
          // In simple score input mode, check if the score can be achieved with a valid finishing dart
          if (!this.isValidOutScore(scoreThrown)) {
            return true;
          }
        }
      }

      // Double out: can't finish on 1
      if (this.gameSettings.outType === 'double' && newScore === 1) {
        return true;
      }

      return false;
    },

    // New method to check if a specific dart is a valid finishing dart
    isValidFinishingDart(dart) {
      if (this.gameSettings.outType === 'single') return true;

      if (this.gameSettings.outType === 'double') {
        return dart.type === 'double' || dart.value === 50;
      }

      if (this.gameSettings.outType === 'royal') {
        return dart.type === 'treble' || dart.type === 'double' ||
               (this.gameSettings.boardType === 'quadro' && dart.type === 'quad');
      }

      return true;
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

    isValidFinish(player) {
      const remaining = player.currentScore;
      const maxCheckout = this.getMaxCheckout();

      if (remaining <= 0 || remaining > maxCheckout) return false;

      // For double out games, check if it's a valid finish
      if (this.gameSettings.outType === 'double') {
        return this.canFinishWithDouble(remaining);
      }

      // For other out types, use simpler logic
      return remaining <= this.maxScore;
    },

    isBogeyFinish(player) {
      const remaining = player.currentScore;
      const maxCheckout = this.getMaxCheckout();

      // Only applies to double out games
      if (this.gameSettings.outType !== 'double') return false;

      // Must be under maximum checkout but impossible to finish
      if (remaining <= 0 || remaining > maxCheckout) return false;

      return !this.canFinishWithDouble(remaining);
    },

    getMaxCheckout() {
      // Calculate maximum possible checkout based on board type and out type
      if (this.gameSettings.outType === 'double') {
        if (this.gameSettings.boardType === 'quadro') {
          // Quadro board: Q20 + Q20 + Bull = 80 + 80 + 50 = 210
          return 210;
        } else {
          // Standard board: T20 + T20 + Bull = 60 + 60 + 50 = 170
          return 170;
        }
      } else {
        // For single out or royal out, use max possible score
        return this.maxScore;
      }
    },

    // Get all possible scoring values for a board type
    getPossibleScores(boardType) {
      const scores = [0]; // Miss

      // Single scores 1-20
      for (let i = 1; i <= 20; i++) {
        scores.push(i);
      }

      // Double scores 2-40
      for (let i = 1; i <= 20; i++) {
        scores.push(i * 2);
      }

      // Triple scores 3-60
      for (let i = 1; i <= 20; i++) {
        scores.push(i * 3);
      }

      // Bull scores
      scores.push(25, 50);

      // Quadro scores for quadro boards
      if (boardType === 'quadro') {
        for (let i = 1; i <= 20; i++) {
          scores.push(i * 4);
        }
      }

      return [...new Set(scores)].sort((a, b) => a - b);
    },

    // Get all possible double/finishing scores for a board type
    getFinishingScores(boardType) {
      const finishingScores = [50]; // Bullseye only

      // Double scores 2-40
      for (let i = 1; i <= 20; i++) {
        finishingScores.push(i * 2);
      }

      // For royal out games, add trebles as valid finishes
      if (this.gameSettings.outType === 'royal') {
        for (let i = 1; i <= 20; i++) {
          finishingScores.push(i * 3); // Trebles
        }

        // For quadro boards in royal out, also add quadros
        if (boardType === 'quadro') {
          for (let i = 1; i <= 20; i++) {
            finishingScores.push(i * 4); // Quadros
          }
        }
      }

      return [...new Set(finishingScores)].sort((a, b) => a - b);
    },

    canFinishWithDouble(score) {
      const maxCheckout = this.getMaxCheckout();

      // Special cases
      if (score === 50) return true; // Bullseye
      if (score > maxCheckout) return false; // Impossible in 3 darts
      if (score === 1) return false; // Can't finish on 1 in double out

      // Use dynamic calculation for both board types
      return this.canFinishDynamically(score);
    },

    canFinishDynamically(targetScore) {
      const possibleScores = this.getPossibleScores(this.gameSettings.boardType);
      const finishingScores = this.getFinishingScores(this.gameSettings.boardType);

      // Check all combinations of 3 darts where the last dart is a double/bull
      for (let dart1 of possibleScores) {
        for (let dart2 of possibleScores) {
          for (let finishDart of finishingScores) {
            if (dart1 + dart2 + finishDart === targetScore) {
              return true;
            }
          }
        }
      }

      // Check combinations of 2 darts where the last dart is a double/bull
      for (let dart1 of possibleScores) {
        for (let finishDart of finishingScores) {
          if (dart1 + finishDart === targetScore) {
            return true;
          }
        }
      }

      // Check single dart finish
      for (let finishDart of finishingScores) {
        if (finishDart === targetScore) {
          return true;
        }
      }

      return false;
    },

    canFinishWithDoubleQuadro(score) {
      // This method is now redundant as we use canFinishDynamically
      return this.canFinishDynamically(score);
    },

    getPlayerAverage(player) {
      if (player.dartsThrown === 0) return '0.0';

      // In team mode, use individual player's actual scoring
      if (this.gameSettings.enableTeams && player.totalScoreThrown !== undefined) {
        return (player.totalScoreThrown / player.dartsThrown * 3).toFixed(1);
      }

      // In individual mode, use the traditional calculation
      return ((player.target - player.currentScore) / player.dartsThrown * 3).toFixed(1);
    },

    getTeamAverage(team) {
      if (team.dartsThrown === 0) return '0.0';
      return ((team.target - team.currentScore) / team.dartsThrown * 3).toFixed(1);
    },

    getTeamPlayers(team) {
      return this.players.filter(player => player.team === team.id);
    },

    isCurrentPlayer(player) {
      return this.players[this.currentPlayerIndex] && this.players[this.currentPlayerIndex].id === player.id;
    },

    isUpNextPlayer(player) {
      if (!this.gameSettings.enableTeams) return false;

      const currentPlayer = this.players[this.currentPlayerIndex];
      const currentTeam = currentPlayer.teamObject;

      // Move to next team
      const activeTeams = this.teams.filter(team => team.players.length > 0 && !team.isOut);
      const currentTeamIndex = activeTeams.findIndex(team => team.id === currentTeam.id);
      const nextTeamIndex = (currentTeamIndex + 1) % activeTeams.length;
      const nextTeam = activeTeams[nextTeamIndex];

      // Get the next player from that team
      const nextPlayerIndex = nextTeam.currentPlayerIndex % nextTeam.players.length;
      const nextTeamPlayer = nextTeam.players[nextPlayerIndex];

      return player.id === nextTeamPlayer.id;
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
      if (dart.type === 'bull') return '50'; // Handle bullseye specially

      const baseValue = dart.value / (dart.type === 'double' ? 2 : dart.type === 'treble' ? 3 : dart.type === 'quad' ? 4 : 1);
      return `${dart.type.charAt(0).toUpperCase()}${baseValue}`;
    },

    clearThrows() {
      this.currentThrows = [null, null, null];
      this.currentThrowIndex = 0;
      this.selectedNumber = null; // Reset selected number when clearing throws
    },

    // New method for mobile-friendly dart input
    addDartScore(value, type) {
      if (this.currentThrowIndex >= 3) return;

      // Add the dart to current throws
      this.currentThrows[this.currentThrowIndex] = { value, type };
      this.currentThrowIndex++;

      // Reset selected number for next dart
      this.selectedNumber = null;
    },

    // New method to show SweetAlert popover for multiplier selection
    async showMultiplierPopover(num) {
      if (this.currentThrowIndex >= 3) return;

      // Build HTML for custom buttons
      let buttonsHtml = '<div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">';

      if (num >= 1 && num <= 20) {
        // Regular numbers 1-20 with all multipliers
        buttonsHtml += `<button class="swal2-confirm swal2-styled" data-value="single-${num}" style="margin: 0; padding: 12px; font-size: 16px;">Single ${num} (${num})</button>`;
        buttonsHtml += `<button class="swal2-confirm swal2-styled" data-value="double-${num}" style="margin: 0; padding: 12px; font-size: 16px;">Double ${num} (${num * 2})</button>`;
        buttonsHtml += `<button class="swal2-confirm swal2-styled" data-value="treble-${num}" style="margin: 0; padding: 12px; font-size: 16px;">Treble ${num} (${num * 3})</button>`;

        if (this.gameSettings.boardType === 'quadro') {
          buttonsHtml += `<button class="swal2-confirm swal2-styled" data-value="quad-${num}" style="margin: 0; padding: 12px; font-size: 16px;">Quad ${num} (${num * 4})</button>`;
        }
      }

      buttonsHtml += '</div>';

      let multiplierChoice = null;

      try {
        await Swal.fire({
          title: `Select multiplier for ${num}`,
          html: buttonsHtml,
          showConfirmButton: false,
          showCancelButton: true,
          cancelButtonText: 'Cancel',
          showCloseButton: true,
          allowOutsideClick: false,
          customClass: {
            popup: 'swal2-popup-mobile-friendly',
            title: 'swal2-title-mobile',
            htmlContainer: 'swal2-html-mobile',
            cancelButton: 'swal2-cancel-mobile'
          },
          didOpen: () => {
            // Add click handlers to custom buttons
            const buttons = Swal.getPopup().querySelectorAll('button[data-value]');
            buttons.forEach(button => {
              button.addEventListener('click', () => {
                multiplierChoice = button.getAttribute('data-value');
                Swal.close();
              });
            });
          }
        });

        // Process the selection if one was made
        if (multiplierChoice) {
          // Parse the selected multiplier and add the dart score
          const [type, numberStr] = multiplierChoice.split('-');
          const baseNumber = parseInt(numberStr);
          let dartValue, dartType;

          switch (type) {
            case 'single':
              dartValue = baseNumber;
              dartType = 'single';
              break;
            case 'double':
              dartValue = baseNumber * 2;
              dartType = 'double';
              break;
            case 'treble':
              dartValue = baseNumber * 3;
              dartType = 'treble';
              break;
            case 'quad':
              dartValue = baseNumber * 4;
              dartType = 'quad';
              break;
          }

          this.addDartScore(dartValue, dartType);
        }
        // If cancelled or no selection, do nothing (number is deselected automatically)
      } catch (error) {
        // User cancelled or closed the modal, do nothing
        console.log('Multiplier selection cancelled');
      }
    },

    submitScore() {
      // Additional safety check for empty input
      if (this.scoreInput === '' || this.scoreInput === null || this.scoreInput === undefined || !this.isValidScore) {
        return;
      }

      const score = parseInt(this.scoreInput);
      this.processScore(score, false);
      this.scoreInput = 0; // Set to 0 instead of empty string
      this.validateScore(); // Validate the new 0 value
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
        hadStarted: player.hasStarted,
        teamPreviousScore: this.gameSettings.enableTeams && player.teamObject ? player.teamObject.currentScore : null,
        playerPreviousScoreThrown: player.totalScoreThrown || 0,
        playerPreviousDartsThrown: player.dartsThrown
      });

      if (!isBust) {
        const newScore = player.currentScore - score;

        if (this.isBustScore(newScore, score, player)) {
          isBust = true;
        }
        else {
          // Trigger score animation before updating the actual score
          // this.triggerScoreAnimation(player.currentScore, newScore, player.id, isBust);

          // Update scores based on game mode
          if (this.gameSettings.enableTeams && player.teamObject) {
            // Team mode: update team score and sync all team members
            player.teamObject.currentScore = newScore;
            player.teamObject.dartsThrown += dartsUsed;

            if (!player.teamObject.hasStarted && score > 0) {
              player.teamObject.hasStarted = true;
            }

            // Update all players on this team to reflect the new team score
            this.players.forEach(p => {
              if (p.team === player.team) {
                p.currentScore = newScore;
                p.target = player.teamObject.target;
                if (!p.hasStarted && score > 0) {
                  p.hasStarted = true;
                }
              }
            });

            if (newScore === 0) {
              this.teamFinished(player.teamObject);
              return;
            }
          } else {
            // Individual mode: update only this player
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
      } else {
        // Trigger bust animation
        // this.triggerScoreAnimation(player.currentScore, player.currentScore, player.id, true);
      }

      // Update individual player stats (both team and individual mode)
      player.dartsThrown += dartsUsed;
      player.scores.push(isBust ? `Bust (${score})` : score);

      // Track individual scoring for accurate averages
      if (!isBust && score > 0) {
        if (!player.totalScoreThrown) player.totalScoreThrown = 0;
        player.totalScoreThrown += score;
      }

      this.nextPlayer();
    },


    // Add the missing methods that were referenced but not implemented
    async teamFinished(team) {
      // Mark all players on the winning team as finished
      this.players.forEach(player => {
        if (player.team === team.id) {
          player.isOut = true;
        }
      });

      team.isOut = true;

      // Set the current player as the winner representative
      this.winner = this.currentPlayer;

      // Show SweetAlert for win confirmation
      await this.showWinConfirmation(this.currentPlayer);
    },

    async playerFinished(player) {
      player.isOut = true;
      this.winner = player;

      // Show SweetAlert for win confirmation
      await this.showWinConfirmation(player);
    },

    async showWinConfirmation(winningPlayer) {
      try {
        const result = await Swal.fire({
          title: 'Game Finished!',
          html: `
            <div class="text-center">
              <h3 class="text-2xl font-bold text-green-600 mb-2">${winningPlayer.name} Wins!</h3>
              <p class="text-lg mb-2">Finished in ${winningPlayer.dartsThrown} darts</p>
              <p class="text-md mb-4">Average: ${this.getPlayerAverage(winningPlayer)}</p>
              ${winningPlayer.teamName ? `<p class="text-md text-gray-600">Team: ${winningPlayer.teamName}</p>` : ''}
            </div>
          `,
          icon: 'success',
          showCancelButton: true,
          confirmButtonText: 'Accept Win',
          cancelButtonText: 'Undo Last Score',
          confirmButtonColor: '#059669',
          cancelButtonColor: '#dc2626',
          allowOutsideClick: false,
          allowEscapeKey: false
        });

        if (result.isConfirmed) {
          this.gameOver = true;
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          this.undoLastScore();
        }
      } catch (error) {
        console.error('Error showing win confirmation:', error);
        this.gameOver = true;
      }
    },

    nextPlayer() {
      if (this.gameSettings.enableTeams) {
        // Team-based turn logic
        const currentPlayer = this.players[this.currentPlayerIndex];
        const currentTeam = currentPlayer.teamObject;

        // Increment the CURRENT team's player index first
        currentTeam.currentPlayerIndex = (currentTeam.currentPlayerIndex + 1) % currentTeam.players.length;

        // Move to next team
        const activeTeams = this.teams.filter(team => team.players.length > 0 && !team.isOut);
        const currentTeamIndex = activeTeams.findIndex(team => team.id === currentTeam.id);
        const nextTeamIndex = (currentTeamIndex + 1) % activeTeams.length;
        const nextTeam = activeTeams[nextTeamIndex];

        // Get the next player from that team
        const nextPlayerIndex = nextTeam.currentPlayerIndex % nextTeam.players.length;
        const nextTeamPlayer = nextTeam.players[nextPlayerIndex];

        // Find this player in the main players array
        const nextPlayerInArray = this.players.find(p => p.id === nextTeamPlayer.id);
        this.currentPlayerIndex = this.players.indexOf(nextPlayerInArray);
      } else {
        // Individual play: smooth transition
        this.performSmoothTransition();
      }

      // Focus the input field for the next player
      if (this.gameSettings.enableTeams) {
        this.focusScoreInput();
      }
    },

    async performSmoothTransition() {
      // Start transition
      this.isTransitioning = true;

      // Move to next player
      this.currentPlayerIndex = (this.currentPlayerIndex + 1) % this.players.length;

      // Wait for transition to complete
      await new Promise(resolve => setTimeout(resolve, 600));

      // End transition
      this.isTransitioning = false;

      // Focus input
      this.focusScoreInput();
    },

    focusScoreInput() {
      this.$nextTick(() => {
        if (!this.perThrowMode && this.$refs.scoreInputField) {
          this.$refs.scoreInputField.focus();
        }
      });
    },

    onScoreInputFocus() {
      this.scoreInput = 0;
      this.validateScore();
    },

    undoLastScore() {
      if (this.gameHistory.length === 0) return;

      const lastMove = this.gameHistory.pop();
      const player = this.players[lastMove.playerIndex];

      // Restore player state
      player.currentScore = lastMove.previousScore;
      player.dartsThrown = lastMove.playerPreviousDartsThrown;
      player.hasStarted = lastMove.hadStarted;
      player.isOut = false;
      player.scores.pop();

      // Restore individual scoring data for team mode
      if (this.gameSettings.enableTeams && lastMove.playerPreviousScoreThrown !== undefined) {
        player.totalScoreThrown = lastMove.playerPreviousScoreThrown;
      }

      // Handle team-specific undo
      if (this.gameSettings.enableTeams && player.teamObject && lastMove.teamPreviousScore !== null) {
        player.teamObject.currentScore = lastMove.teamPreviousScore;
        player.teamObject.dartsThrown -= lastMove.dartsUsed;
        player.teamObject.isOut = false;

        // Update all players on this team
        this.players.forEach(p => {
          if (p.team === player.team) {
            p.currentScore = lastMove.teamPreviousScore;
            p.isOut = false;
          }
        });
      }

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

      // Reset player targets to default
      this.startPlayers.forEach(player => {
        this.playerTargets[player.id] = this.gameSettings.defaultTarget;
      });

      // Clear custom players
      this.customPlayers = [];

      // Re-initialize teams
      this.initializeTeams();
    },

    addCustomPlayer() {
      const name = this.newPlayerName.trim();
      if (!name) return;

      this.customPlayers.push({
        name,
        target: this.gameSettings.defaultTarget
      });

      this.newPlayerName = '';
    },

    removeCustomPlayer(index) {
      this.customPlayers.splice(index, 1);
    },

    initializeTeams() {
      this.teams = Array.from({ length: this.gameSettings.numberOfTeams }, (_, i) => ({
        id: `team${i + 1}`,
        name: `Team ${i + 1}`,
        players: [],
        currentScore: this.gameSettings.defaultTarget,
        target: this.gameSettings.defaultTarget,
        dartsThrown: 0,
        hasStarted: false,
        isOut: false
      }));
    },

    toggleStatsView() {
      this.showStats = !this.showStats;
    },

    // Add other missing helper methods
    selectTargetScore(value) {
      if (typeof value === 'number') {
        // Preset score selected
        this.gameSettings.targetScoreType = 'preset';
        this.gameSettings.defaultTarget = value;
        this.gameSettings.customTarget = 0;
      } else if (value === 'other') {
        // Other option selected
        this.gameSettings.targetScoreType = 'other';
        if (this.gameSettings.customTarget > 0) {
          this.gameSettings.defaultTarget = this.gameSettings.customTarget;
        }
      }
    },

    updateAllTargetScores(newTarget) {
      // Update player targets
      this.startPlayers.forEach(player => {
        this.playerTargets[player.id] = newTarget;
      });

      // Update custom players
      this.customPlayers.forEach(player => {
        player.target = newTarget;
      });

      // Update teams
      this.teams.forEach(team => {
        team.target = newTarget;
        team.currentScore = newTarget;
      });
    },

    // Sets/Legs helper methods
    getValidSetsNumbers() {
      return this.gameSettings.setsFormat === 'bestOf'
        ? [1, 3, 5, 7, 9, 11, 13]
        : [1, 2, 3, 4, 5, 6, 7];
    },

    getValidLegsNumbers() {
      return this.gameSettings.legsFormat === 'bestOf'
        ? [1, 3, 5, 7, 9, 11, 13]
        : [1, 2, 3, 4, 5, 6, 7];
    },

    updateSetsNumber() {
      this.gameSettings.setsNumber = this.getValidSetsNumbers()[0];
    },

    updateLegsNumber() {
      this.gameSettings.legsNumber = this.getValidLegsNumbers()[0];
    },

    getSetsDescription() {
      if (this.gameSettings.setsFormat === 'bestOf') {
        const toWin = Math.ceil(this.gameSettings.setsNumber / 2);
        return `First to win ${toWin} sets`;
      } else {
        return `First to win ${this.gameSettings.setsNumber} sets`;
      }
    },

    getLegsDescription() {
      if (this.gameSettings.legsFormat === 'bestOf') {
        const toWin = Math.ceil(this.gameSettings.legsNumber / 2);
        return `First to win ${toWin} legs`;
      } else {
        return `First to win ${this.gameSettings.legsNumber} legs`;
      }
    },

    // Team management methods
    onTeamModeToggle() {
      if (!this.gameSettings.enableTeams) {
        // Clear all team assignments when disabling teams
        this.teams.forEach(team => {
          team.players = [];
        });
      }
    },

    onTeamCountChange() {
      this.initializeTeams();
    },

    getTeamGridClass() {
      return {
        'grid-cols-2': this.gameSettings.numberOfTeams === 2,
        'grid-cols-3': this.gameSettings.numberOfTeams === 3,
        'grid-cols-2 lg:grid-cols-4': this.gameSettings.numberOfTeams === 4
      };
    },

    getTeamColorClass(teamNumber) {
      const colors = {
        1: 'border-blue-300 bg-blue-50',
        2: 'border-red-300 bg-red-50',
        3: 'border-green-300 bg-green-50',
        4: 'border-yellow-300 bg-yellow-50'
      };
      return colors[teamNumber] || 'border-gray-300 bg-gray-50';
    },

    getTeamScore(teamIndex) {
      return this.teams[teamIndex]?.currentScore || this.gameSettings.defaultTarget;
    },

    // Drag and drop methods
    onDragStart(event, player) {
      event.dataTransfer.setData('application/json', JSON.stringify(player));
    },

    onDrop(event, teamIndex) {
      event.preventDefault();
      const playerData = JSON.parse(event.dataTransfer.getData('application/json'));

      // Remove player from current team (if any)
      this.teams.forEach(team => {
        team.players = team.players.filter(p => p.id !== playerData.id);
      });

      // Add player to new team
      const teamPlayer = {
        id: playerData.id,
        name: playerData.name || playerData.title,
        type: playerData.type
      };

      this.teams[teamIndex].players.push(teamPlayer);
    },

    removePlayerFromTeam(teamIndex, playerIndex) {
      this.teams[teamIndex].players.splice(playerIndex, 1);
    },

    // Player carousel helper methods
    isPreviousPlayer(player) {
      if (this.players.length <= 1) return false;
      const prevIndex = this.currentPlayerIndex === 0
        ? this.players.length - 1
        : this.currentPlayerIndex - 1;
      return this.players[prevIndex] && this.players[prevIndex].id === player.id;
    },

    isNextPlayer(player) {
      if (this.players.length <= 1) return false;
      const nextIndex = (this.currentPlayerIndex + 1) % this.players.length;
      return this.players[nextIndex] && this.players[nextIndex].id === player.id;
    },

    isDistantPlayer(player) {
      return !this.isCurrentPlayer(player) && !this.isPreviousPlayer(player) && !this.isNextPlayer(player);
    }
  }
}
</script>

<style scoped>
/* Add basic styling for the component */
.n01-scorer {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
  color: #0c4a6e; /* text-sky-800 */
}

.fullscreen-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #f0f9ff; /* bg-sky-50 */
  z-index: 1000;
  overflow-y: auto;
}

.game-interface {
  padding: 1rem;
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.current-player-display {
  flex: 0 0 auto;
  margin-bottom: 2rem;
}

.player-carousel {
  overflow: hidden;
  position: relative;
  height: 200px;
}

.carousel-container {
  display: flex;
  transition: transform 0.3s ease-out;
}

.player-slide {
  flex: 0 0 250px;
  margin-right: 16px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #e0f2fe; /* sky-100 */
}

.player-slide.current {
  transform: scale(1.1);
  box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.2);
  border: 2px solid #2563eb; /* blue-600 */
  background: #f0f9ff; /* sky-50 */
}

.player-content {
  text-align: center;
}

.player-name {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
  color: #0c4a6e; /* text-sky-800 */
}

.remaining-score {
  font-size: 3rem;
  font-weight: bold;
  color: #0c4a6e; /* text-sky-800 */
  margin-bottom: 0.5rem;
}

.team-name {
  color: #075985; /* text-sky-700 */
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.current-indicator {
  color: #2563eb; /* blue-600 */
  font-weight: 600;
  font-size: 0.875rem;
}

.player-stats {
  color: #0369a1; /* text-sky-600 */
  font-size: 0.875rem;
}

.scoring-section {
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.score-input-row {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.score-input {
  flex: 1;
  padding: 1rem;
  font-size: 1.5rem;
  border: 2px solid #bae6fd; /* sky-200 */
  border-radius: 8px;
  text-align: center;
  background: white;
  color: #0c4a6e; /* text-sky-800 */
}

.score-input:focus {
  outline: none;
  border-color: #2563eb; /* blue-600 */
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.submit-btn, .bust-btn, .clear-btn {
  padding: 1rem 2rem;
  font-size: 1.25rem;
  font-weight: 600;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.submit-btn {
  background: #0284c7; /* sky-600 */
  color: white;
}

.submit-btn:hover:not(:disabled) {
  background: #0369a1; /* sky-700 */
}

.submit-btn:disabled {
  background: #94a3b8; /* slate-400 */
  cursor: not-allowed;
}

.bust-btn {
  background: #075985; /* sky-800 */
  color: white;
}

.bust-btn:hover {
  background: #0c4a6e; /* sky-900 */
}

.clear-btn {
  background: #64748b; /* slate-500 */
  color: white;
}

.clear-btn:hover {
  background: #475569; /* slate-600 */
}

.validation-message {
  margin-top: 0.5rem;
  font-size: 0.875rem;
}

.darts-display {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-bottom: 1rem;
}

.dart-slot {
  background: white;
  border: 2px solid #bae6fd; /* sky-200 */
  border-radius: 8px;
  padding: 1rem;
  text-align: center;
  min-width: 80px;
}

.dart-label {
  display: block;
  font-size: 0.875rem;
  color: #0369a1; /* text-sky-600 */
  margin-bottom: 0.5rem;
}

.dart-value {
  font-size: 1.25rem;
  font-weight: bold;
  color: #0c4a6e; /* text-sky-800 */
}

.numbers-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.number-btn, .special-btn {
  padding: 1rem;
  font-size: 1.25rem;
  font-weight: bold;
  background: white;
  border: 2px solid #bae6fd; /* sky-200 */
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  color: #0c4a6e; /* text-sky-800 */
}

.number-btn:hover, .special-btn:hover {
  background: #f0f9ff; /* sky-50 */
  border-color: #0284c7; /* sky-600 */
}

.special-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
  margin-bottom: 1rem;
}

.throw-controls {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.quick-controls {
  flex: 0 0 auto;
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 1rem;
}

.undo-btn, .stats-btn, .reset-btn {
  padding: 0.75rem 1.5rem;
  font-weight: 600;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.undo-btn {
  background: #0891b2; /* cyan-600 */
  color: white;
}

.undo-btn:hover:not(:disabled) {
  background: #0e7490; /* cyan-700 */
}

.undo-btn:disabled {
  background: #94a3b8; /* slate-400 */
  cursor: not-allowed;
}

.stats-btn {
  background: #2563eb; /* blue-600 */
  color: white;
}

.stats-btn:hover {
  background: #1d4ed8; /* blue-700 */
}

.reset-btn {
  background: #075985; /* sky-800 */
  color: white;
}

.reset-btn:hover {
  background: #0c4a6e; /* sky-900 */
}

.stats-section {
  margin-top: 1rem;
  background: #f8fafc; /* slate-50 */
  border-radius: 8px;
  padding: 1rem;
  border: 1px solid #e2e8f0; /* slate-200 */
}

.team-card {
  background: white;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
  border: 1px solid #e0f2fe; /* sky-100 */
}

.team-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #e2e8f0; /* slate-200 */
}

.individual-scoreboard {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e0f2fe; /* sky-100 */
}

.scoreboard-header {
  background: #f8fafc; /* slate-50 */
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 1rem;
  padding: 1rem;
  font-weight: bold;
  color: #0c4a6e; /* text-sky-800 */
}

.player-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0; /* slate-200 */
}

.player-row.current-player-row {
  background: #dbeafe; /* blue-100 */
}

.player-row.out-player-row {
  opacity: 0.5;
}

.finish-score {
  color: #059669; /* emerald-600 */
  font-weight: 600;
}

.bogey-score {
  color: #dc2626; /* red-600 */
  font-weight: 600;
}

.game-over-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  max-width: 400px;
  width: 90%;
  border: 2px solid #bae6fd; /* sky-200 */
}

.modal-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 1.5rem;
}

.new-game-btn, .close-btn {
  padding: 0.75rem 1.5rem;
  font-weight: 600;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.new-game-btn {
  background: #0284c7; /* sky-600 */
  color: white;
}

.new-game-btn:hover {
  background: #0369a1; /* sky-700 */
}

.close-btn {
  background: #64748b; /* slate-500 */
  color: white;
}

.close-btn:hover {
  background: #475569; /* slate-600 */
}

/* Team setup styles */
.team-drop-zone {
  transition: all 0.2s;
}

.team-drop-zone:hover {
  border-color: #2563eb; /* blue-600 */
  background-color: rgba(37, 99, 235, 0.05);
}

.player-card {
  transition: all 0.2s;
}

.player-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .score-input-row {
    flex-direction: column;
  }

  .numbers-grid {
    grid-template-columns: repeat(5, 1fr);
  }

  .quick-controls {
    flex-direction: column;
  }

  .scoreboard-header,
  .player-row {
    grid-template-columns: 1.5fr 1fr 1fr 1fr;
    font-size: 0.875rem;
  }
}
</style>
