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
        <div v-if="gameSettings.enableTeams" class="space-y-4">
          <div class="flex items-center space-x-4">
            <label class="text-lg font-medium text-sky-800">Number of Teams:</label>
            <select v-model.number="gameSettings.numberOfTeams" @change="onTeamCountChange" class="p-2 border rounded ring-1 ring-sky-200 focus:ring-blue-600 bg-white">
              <option value="2">2 Teams</option>
              <option value="3">3 Teams</option>
              <option value="4">4 Teams</option>
            </select>
          </div>
          <div class="grid gap-4" :class="getTeamGridClass()">
            <div v-for="(team, teamIndex) in teams" :key="team.id"
                 class="team-drop-zone border-2 border-dashed border-sky-300 rounded-lg p-4 min-h-[200px] bg-sky-50"
                 :class="getTeamColorClass(teamIndex + 1)"
                 @drop="onDrop($event, teamIndex)"
                 @dragover.prevent
                 @dragenter.prevent>
              <div class="mb-3">
                <input v-model="team.name" :placeholder="`Team ${teamIndex + 1}`" class="w-full p-2 border rounded font-medium text-center ring-1 ring-sky-200 focus:ring-blue-600">
              </div>
              <div v-if="team.players.length > 0" class="mb-2 text-center">
                <div class="text-sm text-sky-600">Team Score</div>
                <div class="text-lg font-bold text-sky-800">{{ getTeamScore(teamIndex) }}</div>
              </div>
              <div class="space-y-2">
                <h5 class="font-medium text-sm text-sky-600 text-center">Players ({{ team.players.length }})</h5>
                <div v-for="(player, playerIndex) in team.players" :key="player.id || `${player.name}-${playerIndex}`" class="bg-white p-2 rounded shadow-sm border ring-1 ring-sky-200 flex items-center justify-between">
                  <span class="font-medium text-sky-800">{{ player.name }}</span>
                  <div class="flex items-center space-x-2">
                    <span class="text-xs text-sky-500">#{{ playerIndex + 1 }}</span>
                    <button @click="removePlayerFromTeam(teamIndex, playerIndex)" class="text-sky-600 hover:text-sky-800 text-sm">×</button>
                  </div>
                </div>
                <p v-if="team.players.length === 0" class="text-sky-400 text-sm text-center italic">Drag players here</p>
              </div>
            </div>
          </div>
          <div class="mt-6">
            <h4 class="text-lg font-medium mb-3 text-sky-700">Available Players</h4>
            <div class="flex flex-wrap gap-2 p-4 border-2 border-dashed border-sky-200 rounded-lg min-h-[80px] bg-sky-50">
              <div v-for="player in unassignedStartPlayers" :key="player.id" class="player-card bg-sky-100 text-sky-800 px-3 py-2 rounded-full cursor-move flex items-center ring-1 ring-sky-300" draggable="true" @dragstart="onDragStart($event, { ...player, name: player.title, type: 'selected' })">
                <span>{{ player.title }}</span>
              </div>
              <div v-for="(player, index) in unassignedCustomPlayers" :key="'custom-' + index" class="player-card bg-sky-100 text-sky-800 px-3 py-2 rounded-full cursor-move flex items-center ring-1 ring-sky-300" draggable="true" @dragstart="onDragStart($event, { ...player, type: 'custom', originalIndex: index, id: `custom-${player.name}` })">
                <span>{{ player.name }}</span>
              </div>
              <p v-if="unassignedStartPlayers.length === 0 && unassignedCustomPlayers.length === 0" class="text-sky-400 text-sm italic w-full text-center">All players assigned to teams</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Mugs Away Option (only for exactly 2 participants: either 2 players or 2 teams) -->
      <div v-if="canUseMugsAway" class="mb-6 bg-white p-4 rounded border ring-1 ring-sky-200 flex items-center justify-between">
        <label class="flex items-center space-x-3 cursor-pointer">
          <input type="checkbox" v-model="gameSettings.mugsAway" class="rounded border-sky-300 text-blue-600 focus:ring-blue-500">
          <span class="text-lg font-medium text-sky-800">Mugs Away (loser starts next leg)</span>
        </label>
        <span class="text-sm text-sky-600">Available only in 2 player / 2 team games</span>
      </div>

      <!-- Game Controls -->
      <div class="flex justify-between items-center mb-6">
        <button
          @click="startGame"
          :disabled="totalPlayers === 0"
          class="bg-sky-600 text-white px-6 py-3 rounded-lg text-lg font-medium hover:bg-sky-700 transition-colors disabled:opacity-50"
        >
          Start Game
        </button>
      </div>
    </div>

    <!-- Game Interface - Full Screen Overlay When Playing -->
    <div v-if="gameStarted" class="game-interface h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 text-white overflow-hidden flex flex-col">
      <!-- Current Leg / Match Info -->
      <div class="flex items-center justify-center py-4 gap-6 text-blue-100 font-medium border-b border-blue-800/30" v-if="!gameOver">
        <div class="text-lg">Leg {{ currentLeg }}</div>
        <div v-if="legsToWin" class="text-sm opacity-80">First to {{ legsToWin }} legs</div>
        <div class="flex gap-4 text-sm" v-if="showLegScores">
          <template v-if="!gameSettings.enableTeams">
            <span v-for="p in players" :key="p.id" class="bg-blue-800/30 px-2 py-1 rounded">{{ p.name }}: {{ legWins[p.id] || 0 }}</span>
          </template>
          <template v-else>
            <span v-for="t in activeTeams" :key="t.id" class="bg-blue-800/30 px-2 py-1 rounded">{{ t.name }}: {{ legWins[t.id] || 0 }}</span>
          </template>
        </div>
      </div>

      <!-- Current Player Display - TOP PRIORITY -->
      <div class="current-player-display flex-shrink-0">
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
              <div class="player-content bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl p-6 text-center shadow-xl">
                <h2 class="player-name text-xl font-bold text-white mb-2">{{ player.name }}</h2>
                <div class="remaining-score text-4xl font-bold text-blue-300 mb-2">{{ player.currentScore }}</div>
                <p v-if="gameSettings.enableTeams && player.teamName" class="team-name text-sm text-blue-200 mb-2">
                  {{ player.teamName }}
                </p>
                <div v-if="isCurrentPlayer(player)" class="current-indicator">
                  <span class="indicator-text bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium animate-pulse">Current Player</span>
                </div>
                <div v-else class="player-stats text-xs text-blue-200 space-y-1">
                  <div class="darts-thrown">{{ player.dartsThrown }} darts</div>
                  <div class="average">{{ getPlayerAverage(player) }} avg</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Scoring Input - SECOND PRIORITY -->
      <div class="scoring-section flex-1 flex flex-col justify-center max-w-4xl mx-auto w-full px-4">
        <div class="mode-toggle mb-6 text-center">
          <label class="inline-flex items-center space-x-3 bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg px-4 py-2">
            <input type="checkbox" v-model="perThrowMode" class="rounded border-blue-300 text-blue-600 focus:ring-blue-500 bg-white/20">
            <span class="text-blue-100 font-medium">Per Throw Mode</span>
          </label>
        </div>

        <!-- Simple Score Input -->
        <div v-if="!perThrowMode" class="simple-scoring bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl p-6">
          <div class="score-input-row flex flex-col sm:flex-row items-center justify-center gap-4 mb-4">
            <input
                ref="scoreInputField"
                type="number"
                v-model.number="scoreInput"
                :min="0"
                :max="maxScore"
                class="score-input w-full sm:w-48 h-16 text-center text-2xl font-bold bg-white/20 border border-white/30 rounded-lg text-white placeholder-blue-200 focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                @input="validateScore"
                @keyup.enter="submitScore"
                @focus="onScoreInputFocus"
                placeholder="Enter score"
            >
            <div class="flex gap-3">
              <button
                  @click="submitScore"
                  :disabled="!isValidScore"
                  class="submit-btn bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-lg transition-colors min-w-[100px]"
              >
                Submit
              </button>
              <button
                  @click="submitBust"
                  class="bust-btn bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition-colors min-w-[100px]"
              >
                Bust
              </button>
            </div>
          </div>
          <p class="validation-message text-center text-sm" :class="scoreValidationClass">{{ scoreValidationMessage }}</p>
        </div>

        <!-- Per Throw Input -->
        <div v-if="perThrowMode" class="per-throw-scoring bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl p-6">
          <div class="darts-display flex justify-center gap-4 mb-6">
            <div v-for="(dart, index) in currentThrows" :key="index" class="dart-slot bg-white/20 border border-white/30 rounded-lg p-4 min-w-[80px] text-center">
              <span class="dart-label block text-xs text-blue-200 mb-1">Dart {{ index + 1 }}</span>
              <div class="dart-value text-lg font-bold text-white">
                {{ dart ? formatDartDisplay(dart) : '-' }}
              </div>
            </div>
          </div>

          <!-- Mobile-Friendly Dartboard Input -->
          <div class="dartboard-input mb-6">
            <div class="numbers-grid grid grid-cols-4 sm:grid-cols-5 gap-2 mb-4">
              <button
                v-for="num in sortedNumbers"
                :key="num"
                @click="showMultiplierPopover(num)"
                class="number-btn bg-blue-700 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition-colors"
              >
                {{ num }}
              </button>
            </div>

            <div class="special-buttons flex justify-center gap-3">
              <button @click="addDartScore(25, 'single')" class="special-btn bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">25</button>
              <button @click="addDartScore(50, 'bull')" class="special-btn bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">Bull</button>
              <button @click="addDartScore(0, 'miss')" class="special-btn bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">Miss</button>
            </div>
          </div>

          <div class="throw-controls flex justify-center gap-3">
            <button
                @click="submitThrows"
                :disabled="currentThrows.every(t => t === null)"
                class="submit-btn bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-lg transition-colors"
            >
              Submit Throws
            </button>
            <button @click="clearThrows" class="clear-btn bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">Clear</button>
            <button @click="submitBust" class="bust-btn bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">Bust</button>
          </div>
        </div>
      </div>

      <!-- Game Controls - Quick Access -->
      <div class="quick-controls flex justify-center gap-4 p-4 border-t border-white/20">
        <button @click="undoLastScore" :disabled="gameHistory.length === 0" class="undo-btn bg-orange-600 hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded-lg transition-colors">
          Undo
        </button>
        <button @click="toggleStatsView" class="stats-btn bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
          {{ showStats ? 'Hide Stats' : 'Show Stats' }}
        </button>
        <button @click="resetGame" class="reset-btn bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">Reset</button>
      </div>

      <!-- Collapsible Stats Section -->
      <div v-if="showStats" class="stats-section bg-black/20 backdrop-blur-sm border-t border-white/20 p-4 max-h-80 overflow-y-auto">
        <!-- Team-based Scoreboard -->
        <div v-if="gameSettings.enableTeams" class="team-scoreboard space-y-4">
          <div v-for="team in activeTeams" :key="team.id" class="team-card bg-white/10 border border-white/20 rounded-lg p-4">
            <div class="team-header flex justify-between items-center mb-3">
              <h3 class="text-lg font-bold text-white">{{ team.name }}</h3>
              <div class="team-score text-right">
                <div class="score text-2xl font-bold text-blue-300">{{ team.currentScore }}</div>
                <div class="details text-xs text-blue-200">{{ team.dartsThrown }} darts | {{ getTeamAverage(team) }} avg</div>
              </div>
            </div>
            <div class="team-players space-y-2">
              <div v-for="player in getTeamPlayers(team)" :key="player.id"
                   class="player-row flex justify-between items-center p-2 rounded"
                   :class="{
                     'bg-green-500/20 border border-green-400': isCurrentPlayer(player),
                     'bg-blue-500/20 border border-blue-400': isUpNextPlayer(player),
                     'bg-white/5': !isCurrentPlayer(player) && !isUpNextPlayer(player)
                   }">
                <span class="name font-medium text-white">{{ player.name }}</span>
                <div class="flex items-center gap-4 text-sm">
                  <span class="darts text-blue-200">{{ player.dartsThrown }}</span>
                  <span class="avg text-blue-200">{{ getPlayerAverage(player) }}</span>
                  <span class="status min-w-[60px] text-right">
                    <span v-if="player.isOut" class="out text-gray-400">Out</span>
                    <span v-else-if="isCurrentPlayer(player)" class="playing text-green-400 font-medium">Playing</span>
                    <span v-else-if="isUpNextPlayer(player)" class="next text-blue-400">Next</span>
                    <span v-else class="waiting text-gray-400">Waiting</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Individual Scoreboard -->
        <div v-else class="individual-scoreboard bg-white/10 border border-white/20 rounded-lg overflow-hidden">
          <!-- Header row -->
          <div class="scoreboard-header grid grid-cols-4 gap-4 p-3 bg-white/20 border-b border-white/20">
            <div class="header-cell font-bold text-white">Player</div>
            <div class="header-cell font-bold text-white text-center">Score</div>
            <div class="header-cell font-bold text-white text-center">Darts</div>
            <div class="header-cell font-bold text-white text-center">Average</div>
          </div>

          <!-- Player rows -->
          <div v-for="player in players" :key="player.id"
               class="player-row grid grid-cols-4 gap-4 p-3 border-b border-white/10 last:border-b-0"
               :class="{
                 'bg-green-500/20': isCurrentPlayer(player),
                 'bg-gray-500/20': player.isOut
               }">
            <div class="player-cell name-cell font-medium text-white">{{ player.name }}</div>
            <div class="player-cell score-cell text-center font-bold text-white"
                 :class="[
                   { 'text-green-400': isValidFinish(player) },
                   { 'text-orange-400': isBogeyFinish(player) }
                 ]">
              {{ player.currentScore }}
            </div>
            <div class="player-cell darts-cell text-center text-blue-200">{{ player.dartsThrown }}</div>
            <div class="player-cell average-cell text-center text-blue-200">{{ getPlayerAverage(player) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Game Over Modal -->
    <div v-if="gameOver" class="game-over-modal fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="modal-content bg-white rounded-xl p-8 max-w-md w-full text-center shadow-2xl">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Game Over!</h2>
        <h3 class="text-2xl font-semibold text-green-600 mb-4">{{ winner.name }} Wins!</h3>
        <p class="text-gray-600 mb-2">Final score: {{ winner.target }} in {{ winner.dartsThrown }} darts</p>
        <p v-if="winner.dartsThrown > 0" class="text-gray-600 mb-6">Average: {{ getPlayerAverage(winner) }}</p>
        <div class="modal-buttons flex gap-4 justify-center">
          <button @click="newGame" class="new-game-btn bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">New Game</button>
          <button @click="gameOver = false" class="close-btn bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">Close</button>
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
      // Set default to 0 so we can immediately type over it and always validate from a known state
      scoreInput: 0,
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
        customTarget: 0,
        mugsAway: false
      },

      // Leg / match tracking
      currentLeg: 1,
      legWins: {}, // key: participant id (player id or team id)
      startingParticipantIndex: 0,

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
      showStats: false,

      // Unified participants model
      currentParticipantIndex: 0,
      participants: [] // unified list of participants (teams or single-player pseudo teams)
    }
  },

  computed: {
    maxScore() {
      return this.gameSettings.boardType === 'quadro' ? 240 : 180;
    },


    currentPlayer() {
      return this.players[this.currentPlayerIndex] || {};
    },

    currentParticipant() {
      return this.participants[this.currentParticipantIndex] || null;
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
    },

    legsToWin() {
      // Determine number of legs required to win the match
      const n = this.gameSettings.legsNumber;
      if (!n) return null;
      return this.gameSettings.legsFormat === 'bestOf' ? Math.ceil(n / 2) : n;
    },
    canUseMugsAway() {
      // Exactly two players (no teams) OR exactly two active teams
      if (this.gameSettings.enableTeams) {
        return this.gameSettings.numberOfTeams === 2;
      }
      return this.totalPlayers === 2;
    },
    showLegScores() {
      return Object.keys(this.legWins).length > 0;
    },
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
      // Validate player selection
      if (this.totalPlayers === 0) {
        alert('Please select at least one player');
        return;
      }

      // Team mode validation
      if (this.gameSettings.enableTeams) {
        const teamsWithPlayers = this.teams.filter(team => team.players.length > 0);
        if (teamsWithPlayers.length < 2) {
          alert('Please assign players to at least 2 teams');
          return;
        }
        this.players = this.createTeamPlayers(teamsWithPlayers);
      } else {
        this.players = this.createIndividualPlayers();
      }
      // Build unified participants (teams or individual players as single-player teams)
      this.buildParticipants();
      this.initializeLegTracking();
      this.gameStarted = true;
      this.currentParticipantIndex = 0;
      // Set currentPlayerIndex to first participant's current player
      this.syncCurrentPlayerIndex();
      this.focusScoreInput();
    },
    buildParticipants() {
      if (this.gameSettings.enableTeams) {
        // Use actual teams with players
        this.participants = this.teams
          .filter(t => t.players.length > 0)
          .map(team => ({
            id: team.id,
            name: team.name,
            players: team.players.map(tp => this.players.find(p => p.id === tp.id)).filter(Boolean),
            target: this.gameSettings.defaultTarget,
            currentScore: this.gameSettings.defaultTarget,
            dartsThrown: 0,
            hasStarted: false,
            isOut: false,
            currentPlayerIndex: 0
          }));
      } else {
        // Each player becomes a single-player participant
        this.participants = this.players.map(p => ({
          id: p.id,
          name: p.name,
          players: [p],
          target: p.target,
          currentScore: p.target,
          dartsThrown: 0,
          hasStarted: false,
          isOut: false,
          currentPlayerIndex: 0
        }));
      }
    },
    syncCurrentPlayerIndex() {
      const participant = this.currentParticipant;
      if (!participant) return;
      const currentPlayer = participant.players[participant.currentPlayerIndex];
      if (!currentPlayer) return;
      const idx = this.players.findIndex(p => p.id === currentPlayer.id);
      if (idx !== -1) this.currentPlayerIndex = idx;
    },
    initializeLegTracking() {
      this.currentLeg = 1;
      this.legWins = {};
      if (this.gameSettings.enableTeams) {
        this.activeTeams.forEach(t => { this.$set ? this.$set(this.legWins, t.id, 0) : (this.legWins[t.id] = 0); });
      } else {
        this.players.forEach(p => { this.$set ? this.$set(this.legWins, p.id, 0) : (this.legWins[p.id] = 0); });
      }
      this.startingParticipantIndex = 0; // First leg starts with first participant
    },
    async showWinConfirmation(winningPlayer, participantId) {
      try {
        const result = await Swal.fire({
          title: `Leg Finished!`,
            html: `
            <div class="text-center">
              <h3 class="text-2xl font-bold text-green-600 mb-2">${winningPlayer.name} Wins the Leg!</h3>
              <p class="text-lg mb-2">In ${winningPlayer.dartsThrown} darts</p>
              <p class="text-md mb-4">Average: ${this.getPlayerAverage(winningPlayer)}</p>
              ${winningPlayer.teamName ? `<p class=\"text-md text-gray-600\">Team: ${winningPlayer.teamName}</p>` : ''}
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
          this.handleLegCompletion(participantId, winningPlayer);
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          this.undoLastScore();
        }
      } catch (e) {
        console.error('Error showing win confirmation', e);
        this.handleLegCompletion(participantId, winningPlayer);
      }
    },
    handleLegCompletion(participantId, winningPlayer) {
      if (this.legWins[participantId] === undefined) this.legWins[participantId] = 0;
      this.legWins[participantId]++;
      const legsToWin = this.legsToWin || 1;
      if (this.legWins[participantId] >= legsToWin) {
        this.gameOver = true;
        this.winner = winningPlayer;
        return;
      }
      this.startNextLeg(participantId);
    },
    createIndividualPlayers() {
      return [
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
      teamsWithPlayers.forEach(team => {
        team.currentScore = this.gameSettings.defaultTarget;
        team.target = this.gameSettings.defaultTarget;
        team.dartsThrown = 0;
        team.hasStarted = false;
        team.isOut = false;
        team.currentPlayerIndex = 0;
      });
      teamsWithPlayers.forEach((team, teamIndex) => {
        team.players.forEach((teamPlayer, playerIndex) => {
          players.push({
            id: teamPlayer.id,
            name: teamPlayer.name,
            target: team.target,
            currentScore: team.currentScore,
            dartsThrown: 0,
            totalScoreThrown: 0,
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
      const possibleScores = this.generatePossibleScores();
      return possibleScores.has(score);
    },
    generatePossibleScores() {
      const scores = new Set([0]);
      const singleScores = [];
      for (let i = 1; i <= 20; i++) {
        singleScores.push(i, i * 2, i * 3);
        if (this.gameSettings.boardType === 'quadro') singleScores.push(i * 4);
      }
      singleScores.push(25, 50);
      singleScores.forEach(s => scores.add(s));
      for (let i = 0; i < singleScores.length; i++) {
        for (let j = 0; j < singleScores.length; j++) scores.add(singleScores[i] + singleScores[j]);
      }
      for (let i = 0; i < singleScores.length; i++) {
        for (let j = 0; j < singleScores.length; j++) {
          for (let k = 0; k < singleScores.length; k++) {
            const total = singleScores[i] + singleScores[j] + singleScores[k];
            if (total <= this.maxScore) scores.add(total);
          }
        }
      }
      return scores;
    },
    isBustScore(newScore, scoreThrown, player) {
      if (newScore < 0) return true;
      if (!player.hasStarted && !this.isValidInScore(scoreThrown)) return true;
      if (newScore === 0) {
        if (this.perThrowMode) {
          const lastDart = this.currentThrows.filter(t => t !== null).pop();
          if (lastDart && !this.isValidFinishingDart(lastDart)) return true;
        } else {
          if (this.gameSettings.outType === 'single') return false;
          if (!this.canFinishDynamically(scoreThrown)) return true;
        }
      }
      return this.gameSettings.outType === 'double' && newScore === 1;
    },
    isValidFinishingDart(dart) {
      if (this.gameSettings.outType === 'single') return true;
      if (this.gameSettings.outType === 'double') return dart.type === 'double' || dart.value === 50;
      if (this.gameSettings.outType === 'royal') {
        return dart.type === 'treble' || dart.type === 'double' || (this.gameSettings.boardType === 'quadro' && dart.type === 'quad');
      }
      return true;
    },
    isValidInScore(score) {
      if (this.gameSettings.inType === 'single') return true;
      if (this.gameSettings.inType === 'double') return this.containsDouble(score) || score === 50;
      if (this.gameSettings.inType === 'treble') return this.containsTreble(score);
      return true;
    },
    containsDouble(score) { for (let i = 1; i <= 20; i++) if (score === i * 2) return true; return false; },
    containsTreble(score) { for (let i = 1; i <= 20; i++) if (score === i * 3) return true; return false; },
    containsQuad(score) { for (let i = 1; i <= 20; i++) if (score === i * 4) return true; return false; },
    isValidFinish(player) {
      const remaining = player.currentScore;
      const maxCheckout = this.getMaxCheckout();
      if (remaining <= 0 || remaining > maxCheckout) return false;
      if (this.gameSettings.outType === 'double') return this.canFinishWithDouble(remaining);
      return remaining <= this.maxScore;
    },
    isBogeyFinish(player) {
      const remaining = player.currentScore;
      const maxCheckout = this.getMaxCheckout();
      if (this.gameSettings.outType !== 'double') return false;
      if (remaining <= 0 || remaining > maxCheckout) return false;
      return !this.canFinishWithDouble(remaining);
    },
    getMaxCheckout() {
      if (this.gameSettings.outType === 'double') {
        return this.gameSettings.boardType === 'quadro' ? 210 : 170;
      }
      return this.maxScore;
    },
    getPossibleScores(boardType) {
      const scores = [0];
      for (let i = 1; i <= 20; i++) scores.push(i);
      for (let i = 1; i <= 20; i++) scores.push(i * 2);
      for (let i = 1; i <= 20; i++) scores.push(i * 3);
      scores.push(25, 50);
      if (boardType === 'quadro') { for (let i = 1; i <= 20; i++) scores.push(i * 4); }
      return [...new Set(scores)].sort((a, b) => a - b);
    },
    getFinishingScores(boardType) {
      const finishingScores = [50];
      for (let i = 1; i <= 20; i++) finishingScores.push(i * 2);
      if (this.gameSettings.outType === 'royal') {
        for (let i = 1; i <= 20; i++) finishingScores.push(i * 3);
        if (boardType === 'quadro') for (let i = 1; i <= 20; i++) finishingScores.push(i * 4);
      }
      return [...new Set(finishingScores)].sort((a, b) => a - b);
    },
    canFinishWithDouble(score) { return this.canFinishDynamically(score); },
    canFinishDynamically(targetScore) {
      const possibleScores = this.getPossibleScores(this.gameSettings.boardType);
      const finishingScores = this.getFinishingScores(this.gameSettings.boardType);
      for (let d1 of possibleScores) {
        for (let d2 of possibleScores) {
          for (let f of finishingScores) if (d1 + d2 + f === targetScore) return true;
        }
      }
      for (let d1 of possibleScores) {
        for (let f of finishingScores) if (d1 + f === targetScore) return true;
      }
      for (let f of finishingScores) if (f === targetScore) return true;
      return false;
    },
    canFinishWithDoubleQuadro(score) { return this.canFinishDynamically(score); },
    getPlayerAverage(player) {
      if (player.dartsThrown === 0) return '0.0';
      // Unified: use individual performance (player.totalScoreThrown if present else target-diff)
      if (player.totalScoreThrown !== undefined && player.totalScoreThrown > 0) {
        return (player.totalScoreThrown / player.dartsThrown * 3).toFixed(1);
      }
      return ((player.target - player.currentScore) / player.dartsThrown * 3).toFixed(1);
    },
    getTeamAverage(team) {
      if (team.dartsThrown === 0) return '0.0';
      return ((team.target - team.currentScore) / team.dartsThrown * 3).toFixed(1);
    },
    getTeamPlayers(team) { return this.players.filter(p => p.team === team.id); },
    isCurrentPlayer(player) { return this.players[this.currentPlayerIndex] && this.players[this.currentPlayerIndex].id === player.id; },
    isUpNextPlayer(player) {
      if (!this.gameSettings.enableTeams) return false;
      const currentPlayer = this.players[this.currentPlayerIndex];
      const currentTeam = currentPlayer.teamObject;
      const activeTeams = this.teams.filter(team => team.players.length > 0 && !team.isOut);
      const currentTeamIndex = activeTeams.findIndex(team => team.id === currentTeam.id);
      const nextTeamIndex = (currentTeamIndex + 1) % activeTeams.length;
      const nextTeam = activeTeams[nextTeamIndex];
      const nextPlayerIndex = nextTeam.currentPlayerIndex % nextTeam.players.length;
      const nextTeamPlayer = nextTeam.players[nextPlayerIndex];
      return player.id === nextTeamPlayer.id;
    },
    selectDart(value, type) {
      if (this.currentThrowIndex >= 3) return;
      this.currentThrows[this.currentThrowIndex] = { value, type };
      this.currentThrowIndex++;
    },
    formatDartDisplay(dart) {
      if (dart.type === 'miss') return 'Miss';
      if (dart.type === 'single') return dart.value.toString();
      if (dart.type === 'bull') return '50';
      const base = dart.value / (dart.type === 'double' ? 2 : dart.type === 'treble' ? 3 : dart.type === 'quad' ? 4 : 1);
      return `${dart.type.charAt(0).toUpperCase()}${base}`;
    },
    clearThrows() {
      this.currentThrows = [null, null, null];
      this.currentThrowIndex = 0;
      this.selectedNumber = null;
    },
    addDartScore(value, type) {
      if (this.currentThrowIndex >= 3) return;
      this.currentThrows[this.currentThrowIndex] = { value, type };
      this.currentThrowIndex++;
      this.selectedNumber = null;
    },
    async showMultiplierPopover(num) {
      if (this.currentThrowIndex >= 3) return;
      let buttonsHtml = '<div style="display:flex;flex-direction:column;gap:10px;margin-top:20px;">';
      if (num >= 1 && num <= 20) {
        buttonsHtml += `<button class="swal2-confirm swal2-styled" data-value="single-${num}">Single ${num} (${num})</button>`;
        buttonsHtml += `<button class="swal2-confirm swal2-styled" data-value="double-${num}">Double ${num} (${num*2})</button>`;
        buttonsHtml += `<button class="swal2-confirm swal2-styled" data-value="treble-${num}">Treble ${num} (${num*3})</button>`;
        if (this.gameSettings.boardType === 'quadro') buttonsHtml += `<button class="swal2-confirm swal2-styled" data-value="quad-${num}">Quad ${num} (${num*4})</button>`;
      }
      buttonsHtml += '</div>';
      let choice = null;
      try {
        await Swal.fire({
          title: `Select multiplier for ${num}`,
          html: buttonsHtml,
          showConfirmButton: false,
          showCancelButton: true,
          cancelButtonText: 'Cancel',
          showCloseButton: true,
          allowOutsideClick: false,
          didOpen: () => {
            const buttons = Swal.getPopup().querySelectorAll('button[data-value]');
            buttons.forEach(btn => btn.addEventListener('click', () => { choice = btn.getAttribute('data-value'); Swal.close(); }));
          }
        });
        if (choice) {
          const [type, numberStr] = choice.split('-');
            const base = parseInt(numberStr);
            let dartValue, dartType;
            switch (type) {
              case 'single': dartValue = base; dartType='single'; break;
              case 'double': dartValue = base*2; dartType='double'; break;
              case 'treble': dartValue = base*3; dartType='treble'; break;
              case 'quad': dartValue = base*4; dartType='quad'; break;
            }
            this.addDartScore(dartValue, dartType);
        }
      } catch(_) { /* ignore */ }
    },
    submitScore() {
      if (this.scoreInput === '' || this.scoreInput === null || this.scoreInput === undefined || !this.isValidScore) return;
      const score = parseInt(this.scoreInput);
      this.processScore(score, false);
      // Always reset to 0 and focus for next throw
      this.scoreInput = 0;
      this.validateScore();
      this.focusScoreInput();
    },
    submitThrows() {
      const totalScore = this.currentThrows.filter(t => t !== null).reduce((s,t)=>s+t.value,0);
      this.processScore(totalScore, false);
      this.clearThrows();
    },
    submitBust() {
      this.processScore(0, true);
      if (this.perThrowMode) {
        this.clearThrows();
      } else {
        this.scoreInput = 0; // keep consistent reset to 0
        this.validateScore();
        this.focusScoreInput();
      }
    },
    processScore(score, isBust) {
      const currentParticipant = this.currentParticipant;
      if (!currentParticipant) return;
      const throwingPlayer = currentParticipant.players[currentParticipant.currentPlayerIndex];
      const dartsUsed = this.perThrowMode ? this.currentThrows.filter(t => t !== null).length : 3;

      // History (store participant + player info)
      this.gameHistory.push({
        participantIndex: this.currentParticipantIndex,
        participantPreviousScore: currentParticipant.currentScore,
        playerIndex: this.currentPlayerIndex,
        playerId: throwingPlayer.id,
        previousPlayerScore: throwingPlayer.currentScore,
        score,
        isBust,
        dartsUsed,
        participantHadStarted: currentParticipant.hasStarted,
        playerHadStarted: throwingPlayer.hasStarted,
        participantDartsThrown: currentParticipant.dartsThrown,
        playerDartsThrown: throwingPlayer.dartsThrown,
        participantIsOut: currentParticipant.isOut,
        playerIsOut: throwingPlayer.isOut,
        playerTotalScoreThrown: throwingPlayer.totalScoreThrown || 0
      });

      if (!isBust) {
        const newScore = currentParticipant.currentScore - score;
        if (this.isBustScore(newScore, score, throwingPlayer)) {
          isBust = true;
        } else {
          // Apply score to participant
          currentParticipant.currentScore = newScore;
          if (!currentParticipant.hasStarted && score > 0) currentParticipant.hasStarted = true;
          // Mirror to all players in participant
          currentParticipant.players.forEach(p => {
            p.currentScore = newScore;
            if (!p.hasStarted && score > 0) p.hasStarted = true;
          });
          // Check finish
          if (newScore === 0) {
            // Mark finished
            currentParticipant.isOut = true;
            currentParticipant.players.forEach(p => p.isOut = true);
            this.winner = throwingPlayer;
            this.showWinConfirmation(throwingPlayer, currentParticipant.id);
            return;
          }
        }
      }

      // Update darts & player stats
      currentParticipant.dartsThrown += dartsUsed;
      throwingPlayer.dartsThrown += dartsUsed;
      throwingPlayer.scores.push(isBust ? `Bust (${score})` : score);
      if (!isBust && score > 0) {
        if (!throwingPlayer.totalScoreThrown) throwingPlayer.totalScoreThrown = 0;
        throwingPlayer.totalScoreThrown += score;
      }

      this.nextPlayer();
    },
    undoLastScore() {
      if (this.gameHistory.length === 0) return;
      const last = this.gameHistory.pop();
      const participant = this.participants[last.participantIndex];
      const player = this.players.find(p => p.id === last.playerId);
      if (participant) {
        participant.currentScore = last.participantPreviousScore;
        participant.dartsThrown = last.participantDartsThrown;
        participant.hasStarted = last.participantHadStarted;
        participant.isOut = last.participantIsOut;
        // Mirror back to participant players
        participant.players.forEach(p => {
          p.currentScore = last.participantPreviousScore;
          p.isOut = last.participantIsOut;
        });
      }
      if (player) {
        player.currentScore = last.previousPlayerScore;
        player.dartsThrown = last.playerDartsThrown;
        player.hasStarted = last.playerHadStarted;
        player.isOut = last.playerIsOut;
        player.totalScoreThrown = last.playerTotalScoreThrown;
        // Remove last score entry if matches
        if (player.scores.length) player.scores.pop();
      }
      this.currentParticipantIndex = last.participantIndex;
      this.syncCurrentPlayerIndex();
      this.gameOver = false;
      this.winner = null;
      if (!this.perThrowMode) {
        this.scoreInput = 0;
        this.validateScore();
        this.focusScoreInput();
      }
    },
    nextPlayer() {
      if (this.participants.length === 0) return;
      const participant = this.currentParticipant;
      participant.currentPlayerIndex = (participant.currentPlayerIndex + 1) % participant.players.length;
      this.currentParticipantIndex = (this.currentParticipantIndex + 1) % this.participants.length;
      let guard = 0;
      while (this.participants[this.currentParticipantIndex].isOut && guard < this.participants.length) {
        this.currentParticipantIndex = (this.currentParticipantIndex + 1) % this.participants.length;
        guard++;
      }
      this.syncCurrentPlayerIndex();
      if (!this.perThrowMode) {
        // Always reset score box to 0 for the new player and focus it
        this.scoreInput = 0;
        this.validateScore();
        this.focusScoreInput();
      }
    },
    startNextLeg(lastWinningParticipantId) {
      this.currentLeg++;
      // Determine next starting participant index (supports mugs away when exactly 2 participants)
      if (this.gameSettings.mugsAway && this.participants.length === 2) {
        const winnerIdx = this.participants.findIndex(p => p.id === lastWinningParticipantId);
        this.startingParticipantIndex = winnerIdx === 0 ? 1 : 0;
      } else {
        this.startingParticipantIndex = (this.startingParticipantIndex + 1) % this.participants.length;
      }
      // Reset participants & players
      this.participants.forEach(part => {
        part.currentScore = part.target;
        part.dartsThrown = 0;
        part.hasStarted = false;
        part.isOut = false;
        part.currentPlayerIndex = 0;
        part.players.forEach(p => {
          p.currentScore = p.target;
          p.dartsThrown = 0;
          p.hasStarted = false;
          p.isOut = false;
          p.scores = [];
          p.totalScoreThrown = 0;
        });
      });
      this.currentParticipantIndex = this.startingParticipantIndex;
      this.gameHistory = [];
      this.winner = null;
      this.syncCurrentPlayerIndex();
      this.focusScoreInput();
    },
    resetGame() {
      this.gameStarted = false;
      this.gameOver = false;
      this.winner = null;
      this.currentPlayerIndex = 0;
      this.players = [];
      this.participants = [];
      this.currentParticipantIndex = 0;
      this.gameHistory = [];
      this.scoreInput = 0; // keep consistent
      this.clearThrows();
      this.startPlayers.forEach(player => { this.playerTargets[player.id] = this.gameSettings.defaultTarget; });
      this.customPlayers = [];
      this.initializeTeams();
      this.currentLeg = 1; this.legWins = {}; this.startingParticipantIndex = 0;
    },
    addCustomPlayer() { const name = this.newPlayerName.trim(); if (!name) return; this.customPlayers.push({ name, target: this.gameSettings.defaultTarget }); this.newPlayerName=''; },
    removeCustomPlayer(index) { this.customPlayers.splice(index,1); },
    initializeTeams() { this.teams = Array.from({length: this.gameSettings.numberOfTeams}, (_,i)=>({ id:`team${i+1}`, name:`Team ${i+1}`, players:[], currentScore:this.gameSettings.defaultTarget, target:this.gameSettings.defaultTarget, dartsThrown:0, hasStarted:false, isOut:false })); },
    toggleStatsView() { this.showStats = !this.showStats; },
    selectTargetScore(value) {
      if (typeof value === 'number') {
        this.gameSettings.targetScoreType = 'preset';
        this.gameSettings.defaultTarget = value;
        this.gameSettings.customTarget = 0;
        this.updateAllTargetScores && this.updateAllTargetScores(value);
      } else if (value === 'other') {
        this.gameSettings.targetScoreType = 'other';
      }
    },
    focusScoreInput() {
      this.$nextTick(() => {
        const el = this.$refs.scoreInputField;
        if (el) {
          el.focus();
          try {
            el.setSelectionRange(el.value.length, el.value.length);
          } catch (_) {}
        }
      });
    },
    onScoreInputFocus(e) {
      try {
        e.target.select();
      } catch (_) {}
    },
    updateAllTargetScores(newT) {
      if (this.gameStarted) return;
      this.players.forEach(p => {
        p.target = newT;
        p.currentScore = newT;
      });
      this.teams.forEach(t => {
        t.target = newT;
        t.currentScore = newT;
      });
    },
    getValidSetsNumbers() {
      return this.gameSettings.setsFormat === 'bestOf' ? [1, 3, 5, 7, 9] : [1, 2, 3, 4, 5, 6, 7, 8, 9];
    },
    getValidLegsNumbers() {
      return this.gameSettings.legsFormat === 'bestOf' ? [1, 3, 5, 7, 9, 11, 13] : [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];
    },
    updateSetsNumber() {
      const v = this.getValidSetsNumbers();
      if (!v.includes(this.gameSettings.setsNumber)) this.gameSettings.setsNumber = v[0];
    },
    updateLegsNumber() {
      const v = this.getValidLegsNumbers();
      if (!v.includes(this.gameSettings.legsNumber)) this.gameSettings.legsNumber = v[0];
    },
    getSetsDescription() {
      return this.gameSettings.setsFormat === 'bestOf' ? `First to ${Math.ceil(this.gameSettings.setsNumber / 2)} sets` : `First to ${this.gameSettings.setsNumber} sets`;
    },
    getLegsDescription() {
      return this.gameSettings.legsFormat === 'bestOf' ? `First to ${Math.ceil(this.gameSettings.legsNumber / 2)} legs` : `First to ${this.gameSettings.legsNumber} legs`;
    },
    onTeamModeToggle() {
      if (this.gameSettings.enableTeams) this.initializeTeams();
      else this.teams = [];
    },
    onTeamCountChange() {
      this.initializeTeams();
    },
    getTeamGridClass() {
      const n = this.gameSettings.numberOfTeams;
      if (n === 2) return 'grid-cols-1 md:grid-cols-2';
      if (n === 3) return 'grid-cols-1 md:grid-cols-3';
      return 'grid-cols-1 md:grid-cols-4';
    },
    getTeamColorClass(i) {
      const colors = ['bg-sky-50', 'bg-emerald-50', 'bg-amber-50', 'bg-violet-50'];
      return colors[(i - 1) % colors.length];
    },
    getTeamScore(i) {
      const team = this.teams[i];
      return team ? (team.currentScore ?? this.gameSettings.defaultTarget) : this.gameSettings.defaultTarget;
    },
    removePlayerFromTeam(ti, pi) {
      const team = this.teams[ti];
      if (!team) return;
      team.players.splice(pi, 1);
    },
    onDragStart(e, obj) {
      try {
        e.dataTransfer.setData('application/json', JSON.stringify(obj));
      } catch (_) {}
    },
    onDrop(e, ti) {
      try {
        const data = e.dataTransfer.getData('application/json');
        if (!data) return;
        const player = JSON.parse(data);
        const team = this.teams[ti];
        if (!team) return;
        if (team.players.some(p => p.id === player.id)) return;
        team.players.push({ id: player.id, name: player.name });
      } catch (_) {}
    },
    newGame() {
      this.resetGame();
    },
    // Missing carousel helper methods
    isPreviousPlayer(player) {
      const prevIndex = (this.currentPlayerIndex - 1 + this.players.length) % this.players.length;
      return this.players[prevIndex] && this.players[prevIndex].id === player.id;
    },
    isNextPlayer(player) {
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
/* Just minimal carousel positioning - everything else uses Tailwind */
.n01-scorer.fullscreen-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 50;
}

.player-carousel {
  position: relative;
  height: 200px;
  width: 100%;
  overflow: visible;
  padding-top: 28px;
}

.carousel-container {
  position: relative;
  height: 100%;
  white-space: nowrap;
  overflow: visible;
}

.player-slide {
  display: inline-block;
  width: 250px;
  margin: 0 8px;
  transition: all 0.3s ease;
  vertical-align: top;
}

.player-slide.current {
  transform: scale(1.2);
  z-index: 2;
}

.player-slide.prev,
.player-slide.next {
  transform: scale(0.9);
  opacity: 0.7;
}

.player-slide.distant {
  transform: scale(0.8);
  opacity: 0.5;
}

@media (max-width: 768px) {
  .player-carousel {
    height: 150px;
  }

  .player-slide {
    width: 200px;
  }
}

@media (max-width: 480px) {
  .player-slide {
    width: 150px;
    margin: 0 4px;
  }
}
</style>
