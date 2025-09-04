<template>
  <div class="n01-scorer">
    <!-- Game Setup Section -->
    <div v-if="!gameStarted" class="bg-gray-100 p-6 rounded-lg mb-6">
      <h2 class="text-3xl mb-4">Game Setup</h2>

      <!-- Player Management -->
      <div class="mb-6">
        <h3 class="text-2xl mb-3">Players</h3>

        <!-- Show selected players from scorers page if any -->
        <div v-if="startPlayers.length > 0" class="mb-4">
          <h4 class="text-lg font-medium mb-2">Selected Players:</h4>
          <div class="flex flex-wrap gap-2 mb-3">
            <span v-for="player in startPlayers" :key="player.id"
                  class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
              {{ player.title }}
            </span>
          </div>
        </div>

        <!-- Add custom players section -->
        <div class="bg-white p-4 rounded border">
          <h4 class="text-lg font-medium mb-3">Add Visiting Players</h4>

          <!-- Input for new player -->
          <div class="flex items-center space-x-3 mb-3">
            <input
              type="text"
              v-model="newPlayerName"
              placeholder="Enter player name"
              @keyup.enter="addCustomPlayer"
              class="flex-1 p-2 border rounded"
            >
            <button
              @click="addCustomPlayer"
              :disabled="!newPlayerName.trim()"
              class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors disabled:opacity-50"
            >
              Add Player
            </button>
          </div>

          <!-- List of custom players -->
          <div v-if="customPlayers.length > 0" class="space-y-2">
            <h5 class="font-medium text-gray-700">Visiting Players:</h5>
            <div class="flex flex-wrap gap-2">
              <div v-for="(player, index) in customPlayers" :key="'custom-' + index"
                   class="flex items-center bg-green-100 text-green-800 px-3 py-1 rounded-full">
                <span>{{ player.name }}</span>
                <button
                  @click="removeCustomPlayer(index)"
                  class="ml-2 text-red-600 hover:text-red-800 font-bold"
                  title="Remove player"
                >
                  ×
                </button>
              </div>
            </div>
          </div>

          <!-- Total player count -->
          <div v-if="totalPlayers > 0" class="mt-3 text-sm text-gray-600">
            Total players: {{ totalPlayers }}
          </div>
        </div>
      </div>

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
      <div v-if="totalPlayers >= 3" class="mb-6">
        <label class="flex items-center space-x-2 mb-4">
          <input type="checkbox" v-model="gameSettings.enableTeams" @change="onTeamModeToggle">
          <span class="text-lg font-medium">Enable Team Play</span>
        </label>

        <!-- Team Configuration -->
        <div v-if="gameSettings.enableTeams" class="space-y-4">
          <!-- Number of Teams -->
          <div class="flex items-center space-x-4">
            <label class="text-lg font-medium">Number of Teams:</label>
            <select v-model.number="gameSettings.numberOfTeams" @change="onTeamCountChange" class="p-2 border rounded">
              <option value="2">2 Teams</option>
              <option value="3">3 Teams</option>
              <option value="4">4 Teams</option>
            </select>
          </div>

          <!-- Team Assignment Area -->
          <div class="grid gap-4" :class="getTeamGridClass()">
            <div v-for="(team, teamIndex) in teams" :key="teamIndex"
                 class="team-drop-zone border-2 border-dashed border-gray-300 rounded-lg p-4 min-h-[200px]"
                 :class="getTeamColorClass(teamIndex + 1)"
                 @drop="onDrop($event, teamIndex)"
                 @dragover.prevent
                 @dragenter.prevent>
              <!-- Team Name -->
              <div class="mb-3">
                <input
                  v-model="team.name"
                  :placeholder="`Team ${teamIndex + 1}`"
                  class="w-full p-2 border rounded font-medium text-center"
                >
              </div>

              <!-- Team Score (if team has players) -->
              <div v-if="team.players.length > 0" class="mb-2 text-center">
                <div class="text-sm text-gray-600">Team Score</div>
                <div class="text-lg font-bold">{{ getTeamScore(teamIndex) }}</div>
              </div>

              <!-- Team Players -->
              <div class="space-y-2">
                <h5 class="font-medium text-sm text-gray-600 text-center">
                  Players ({{ team.players.length }})
                </h5>
                <div v-for="(player, playerIndex) in team.players"
                     :key="player.id || `${player.name}-${playerIndex}`"
                     class="bg-white p-2 rounded shadow-sm border flex items-center justify-between">
                  <span class="font-medium">{{ player.name }}</span>
                  <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-500">#{{ playerIndex + 1 }}</span>
                    <button @click="removePlayerFromTeam(teamIndex, playerIndex)"
                            class="text-red-600 hover:text-red-800 text-sm">×</button>
                  </div>
                </div>
                <p v-if="team.players.length === 0"
                   class="text-gray-400 text-sm text-center italic">
                  Drag players here
                </p>
              </div>
            </div>
          </div>

          <!-- Available Players -->
          <div class="mt-6">
            <h4 class="text-lg font-medium mb-3">Available Players</h4>
            <div class="flex flex-wrap gap-2 p-4 border-2 border-dashed border-gray-200 rounded-lg min-h-[80px] bg-gray-50">
              <!-- Selected Players from scorers page -->
              <div v-for="player in unassignedStartPlayers" :key="player.id"
                   class="player-card bg-blue-100 text-blue-800 px-3 py-2 rounded-full cursor-move flex items-center"
                   draggable="true"
                   @dragstart="onDragStart($event, { ...player, name: player.title, type: 'selected' })">
                <span>{{ player.title }}</span>
              </div>

              <!-- Custom/visiting players -->
              <div v-for="(player, index) in unassignedCustomPlayers" :key="'custom-' + index"
                   class="player-card bg-green-100 text-green-800 px-3 py-2 rounded-full cursor-move flex items-center"
                   draggable="true"
                   @dragstart="onDragStart($event, { ...player, type: 'custom', originalIndex: index })">
                <span>{{ player.name }}</span>
              </div>

              <p v-if="unassignedStartPlayers.length === 0 && unassignedCustomPlayers.length === 0"
                 class="text-gray-400 text-sm italic w-full text-center">
                All players assigned to teams
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Handicap Settings -->
      <div v-if="totalPlayers > 0 && !gameSettings.enableTeams" class="mb-6">
        <h3 class="text-xl mb-3">Player Target Scores (Handicap System)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- Selected players from scorers page -->
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
          <!-- Custom/visiting players -->
          <div v-for="(player, index) in customPlayers" :key="'custom-target-' + index"
               class="flex items-center justify-between bg-green-50 p-3 rounded border-green-200">
            <label class="text-lg font-medium">{{ player.name }}:</label>
            <input
                type="number"
                v-model.number="player.target"
                min="1"
                max="9999"
                class="w-24 p-2 border rounded"
            >
          </div>
        </div>
      </div>

      <button
          @click="startGame"
          :disabled="totalPlayers === 0"
          class="bg-green-600 text-white px-6 py-3 rounded-lg text-lg font-medium hover:bg-green-700 transition-colors disabled:opacity-50"
      >
        Start Game
      </button>
    </div>

    <!-- Game Interface -->
    <div v-if="gameStarted">
      <!-- Current Player Display -->
      <div class="bg-blue-100 p-4 rounded-lg mb-6 text-center">
        <h2 class="text-6xl font-bold">{{ currentPlayer.name }} - {{ currentPlayer.currentScore }} remaining</h2>
        <p v-if="gameSettings.enableTeams && currentPlayer.teamName" class="text-lg">
          Playing for {{ currentPlayer.teamName }}
        </p>
      </div>

      <!-- Scoreboard -->
      <div class="overflow-x-auto mb-6">
        <!-- Team-based Scoreboard -->
        <div v-if="gameSettings.enableTeams" class="space-y-6">
          <div v-for="team in activeTeams" :key="team.id" class="bg-white rounded-lg shadow">
            <!-- Team Header -->
            <div class="bg-gray-50 p-4 rounded-t-lg border-b">
              <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold">{{ team.name }}</h3>
                <div class="text-right">
                  <div class="text-sm text-gray-600">Team Score</div>
                  <div class="text-2xl font-bold" :class="{ 'text-green-600': team.currentScore <= 40 }">
                    {{ team.currentScore }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ team.dartsThrown }} darts | Avg: {{ getTeamAverage(team) }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Team Players -->
            <div class="p-0">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="p-3 text-left">Player</th>
                    <th class="p-3 text-center">Darts</th>
                    <th class="p-3 text-center">Average</th>
                    <th class="p-3 text-center">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="player in getTeamPlayers(team)" :key="player.id"
                      :class="{
                        'bg-blue-50': isCurrentPlayer(player),
                        'opacity-50': player.isOut
                      }">
                    <td class="p-3 font-medium">
                      {{ player.name }}
                    </td>
                    <td class="p-3 text-center">{{ player.dartsThrown }}</td>
                    <td class="p-3 text-center">{{ getPlayerAverage(player) }}</td>
                    <td class="p-3 text-center">
                      <span v-if="player.isOut" class="text-red-600 font-medium">Out</span>
                      <span v-else-if="isCurrentPlayer(player)" class="text-green-600 font-medium">Playing</span>
                      <span v-else-if="isUpNextPlayer(player)" class="text-orange-600 font-medium">Up Next</span>
                      <span v-else class="text-gray-500">Waiting</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Individual Scoreboard -->
        <table v-else class="w-full bg-white rounded-lg shadow">
          <thead class="bg-gray-50">
          <tr>
            <th class="p-3 text-left">Player</th>
            <th class="p-3 text-center">Target</th>
            <th class="p-3 text-center">Current Score</th>
            <th class="p-3 text-center">Darts</th>
            <th class="p-3 text-center">Average</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="player in players" :key="player.id" :class="{ 'opacity-50': player.isOut }">
            <td class="p-3 font-medium" :class="{ 'text-2xl': isCurrentPlayer(player) }">{{ player.name }}</td>
            <td class="p-3 text-center" :class="{ 'text-2xl': isCurrentPlayer(player) }">{{ player.target }}</td>
            <td class="p-3 text-center" :class="[
              { 'text-green-600': isValidFinish(player) },
              { 'text-red-600': isBogeyFinish(player) },
              { 'text-2xl': isCurrentPlayer(player) }
            ]">
              {{ player.currentScore }}
            </td>
            <td class="p-3 text-center" :class="{ 'text-2xl': isCurrentPlayer(player) }">{{ player.dartsThrown }}</td>
            <td class="p-3 text-center" :class="{ 'text-2xl': isCurrentPlayer(player) }">{{ getPlayerAverage(player) }}</td>
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
                ref="scoreInputField"
                type="number"
                v-model.number="scoreInput"
                :min="0"
                :max="maxScore"
                class="p-2 border rounded text-lg w-24"
                @input="validateScore"
                @keyup.enter="submitScore"
                @focus="onScoreInputFocus"
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
        enableTeams: false,
        numberOfTeams: 2
      },

      playerTargets: {},
      players: [],
      newPlayerName: '',
      customPlayers: [],
      teams: [],

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

    descendingNumbers() {
      // Generate an array of numbers from 20 to 1
      return Array.from({ length: 20 }, (_, i) => 20 - i);
    },

    activeTeams() {
      // Only include teams that have players assigned
      return this.teams.filter(team => team.players.length > 0);
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
  },

  watch: {
    perThrowMode(newValue) {
      // Focus the input field when switching to simple mode
      if (!newValue) {
        this.focusScoreInput();
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

      if (remaining <= 0 || remaining > 170) return false;

      // Check out rules
      if (this.gameSettings.outType === 'double') {
        if (remaining % 2 !== 0 && remaining !== 25 && remaining !== 50) return false;
        if (remaining > 40 && remaining !== 50) return false;
      }

      return true;
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

      const baseValue = dart.value / (dart.type === 'double' ? 2 : dart.type === 'treble' ? 3 : dart.type === 'quad' ? 4 : 1);
      return `${dart.type.charAt(0).toUpperCase()}${baseValue}`;
    },

    clearThrows() {
      this.currentThrows = [null, null, null];
      this.currentThrowIndex = 0;
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
        teamPreviousScore: this.gameSettings.enableTeams && player.teamObject ? player.teamObject.currentScore : null
      });

      if (!isBust) {
        const newScore = player.currentScore - score;

        if (this.isBustScore(newScore, score, player)) {
          isBust = true;
        }
        else {
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
      }

      // Update individual player stats
      player.dartsThrown += dartsUsed;
      player.scores.push(isBust ? `Bust (${score})` : score);

      this.nextPlayer();
    },

    teamFinished(team) {
      // Mark all players on the winning team as finished
      this.players.forEach(player => {
        if (player.team === team.id) {
          player.isOut = true;
        }
      });

      team.isOut = true;

      // Set the current player as the winner representative
      this.winner = this.currentPlayer;
      this.gameOver = true;
    },

    playerFinished(player) {
      player.isOut = true;
      this.winner = player;
      this.gameOver = true;
    },

    nextPlayer() {
      if (this.gameSettings.enableTeams) {
        // Team-based turn logic
        const currentPlayer = this.players[this.currentPlayerIndex];
        const currentTeam = currentPlayer.teamObject;

        // Increment the CURRENT team's player index first (for next time this team plays)
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
        // Individual play: just cycle through players
        do {
          this.currentPlayerIndex = (this.currentPlayerIndex + 1) % this.players.length;
        } while (this.players[this.currentPlayerIndex].isOut);
      }

      // Focus the input field for the next player
      this.focusScoreInput();
    },

    focusScoreInput() {
      // Use nextTick to ensure DOM is updated before focusing
      this.$nextTick(() => {
        if (!this.perThrowMode && this.$refs.scoreInputField) {
          this.$refs.scoreInputField.focus();
        }
      });
    },

    onScoreInputFocus() {
      // Set the input value to 0 when it gets focus
      this.scoreInput = 0;
      this.validateScore();
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

      // Clear custom players
      this.customPlayers = [];

      // Re-initialize teams
      this.initializeTeams();
    },

    addCustomPlayer() {
      const name = this.newPlayerName.trim();
      if (!name) return;

      // Add to custom players with default target score
      this.customPlayers.push({
        name,
        target: this.gameSettings.defaultTarget
      });

      // Clear input
      this.newPlayerName = '';
    },

    removeCustomPlayer(index) {
      this.customPlayers.splice(index, 1);
    },

    initializeTeams() {
      // Create initial empty teams based on the number of teams setting
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

    getTeamColorClass(teamIndex) {
      // Dynamic class for team color based on index
      const colors = [
        'border-red-300 bg-red-50',
        'border-blue-300 bg-blue-50',
        'border-green-300 bg-green-50',
        'border-yellow-300 bg-yellow-50'
      ];
      return colors[teamIndex - 1] || '';
    },

    getTeamGridClass() {
      const count = this.gameSettings.numberOfTeams;
      return count === 2 ? 'grid-cols-2' : count === 3 ? 'grid-cols-3' : 'grid-cols-4';
    },

    onTeamModeToggle() {
      if (!this.gameSettings.enableTeams) {
        // Reset teams and unassign players
        this.teams.forEach(team => {
          team.players.forEach(player => {
            player.team = null;
          });
          team.players = [];
        });
      }
    },

    onTeamCountChange() {
      const currentCount = this.teams.length;
      const newCount = this.gameSettings.numberOfTeams;

      if (newCount > currentCount) {
        // Add empty teams
        for (let i = currentCount; i < newCount; i++) {
          this.teams.push({
            id: `team${i + 1}`,
            name: `Team ${i + 1}`,
            players: []
          });
        }
      } else {
        // Remove excess teams
        this.teams.splice(newCount);
      }
    },

    isPlayerInTeam(playerId, teamIndex) {
      return this.teams[teamIndex] && this.teams[teamIndex].players.some(p => p.id === playerId);
    },

    getTeamScore(teamIndex) {
      const team = this.teams[teamIndex];
      if (!team || team.players.length === 0) return this.gameSettings.defaultTarget;

      // During setup, show the default target score
      if (!this.gameStarted) {
        return this.gameSettings.defaultTarget;
      }

      // During the game, return the team's current score
      return team.currentScore || this.gameSettings.defaultTarget;
    },

    onDragStart(event, player) {
      // Store the dragged player data
      event.dataTransfer.setData('player', JSON.stringify(player));
    },

    onDrop(event, teamIndex) {
      const playerData = event.dataTransfer.getData('player');
      const player = JSON.parse(playerData);

      const team = this.teams[teamIndex];
      const isFirstPlayer = team.players.length === 0;
      const hasDefaultName = team.name === `Team ${teamIndex + 1}`;

      // Add to team players
      team.players.push({
        id: player.id || `custom-${player.name}`,
        name: player.name
      });

      // Auto-name the team after the first player if it still has the default name
      if (isFirstPlayer && hasDefaultName) {
        team.name = `Team ${player.name}`;
      }
    },

    removePlayerFromTeam(teamIndex, playerIndex) {
      const removedPlayer = this.teams[teamIndex].players[playerIndex];

      // Remove player from team
      this.teams[teamIndex].players.splice(playerIndex, 1);

      // Add back to appropriate source list
      if (removedPlayer.id.startsWith('custom-')) {
        const playerName = removedPlayer.name;
        const existingCustomPlayer = this.customPlayers.find(p => p.name === playerName);
        if (!existingCustomPlayer) {
          this.customPlayers.push({
            name: playerName,
            target: this.gameSettings.defaultTarget
          });
        }
      } else {
        // Check if it's a start player that needs to be added back
        const existingStartPlayer = this.startPlayers.find(p => p.id === removedPlayer.id);
        if (!existingStartPlayer) {
          // This shouldn't happen normally, but we can handle it gracefully
          console.warn('Player not found in original lists:', removedPlayer);
        }
      }
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

/* Team management styles */
.team-drop-zone {
  @apply cursor-pointer transition-all;
}

.team-drop-zone:hover {
  @apply border-gray-400;
}

.player-card {
  @apply transition-transform;
}

.player-card:hover {
  @apply scale-105;
}
</style>
